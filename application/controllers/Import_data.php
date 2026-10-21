<?php defined('BASEPATH') or exit('No direct script access allowed');

use function PHPUnit\Framework\isNull;
abstract class ImportException extends Exception {
    protected $isError = false;

    public function __construct(string $message = "", int $code = 0, Throwable $previous = null, bool $isError = false) {
        $this->isError = $isError;
        parent::__construct( $message, $code, $previous );
    }

    public function isError() :bool
    {
        return $this->isError;
    }
}

class SkipImportException extends ImportException {
    public function __construct(string $message = '', Throwable|null $previous = null, int $code = 0) {
        parent::__construct( $message, $code, $previous, false );
    }
}

class FailImportException extends ImportException {
    public function __construct(string $message = '', Throwable|null $previous = null, int $code = 0) {
        parent::__construct( $message, $code, $previous, true );
    }
}

class Import_data extends EA_Controller
{
    public function __construct() {
        parent::__construct();
        $this->load->model('appointments_model');
        $this->load->model('customers_model');
        $this->load->model('users_model');
        $this->load->model('roles_model');
        $this->load->model('services_model');
        $this->load->model('subservices_model');
        $this->load->model( 'booking_statusses_model' );
    }

    public function index(): void {
        session( [ 'dest_url' => site_url( 'import_data' ) ] );

        $user_id = session( 'user_id' );

        if ( cannot( 'edit', PRIV_SYSTEM_SETTINGS ) ) {
            if ( $user_id ) {
                abort( 403, 'Forbidden' );
            }

            redirect( 'login' );

            return;
        }

        script_vars( [
            'import_systems' => $this->getSystemsList(),
        ] );

        $role_slug = session('role_slug');

        html_vars([
            'page_title' => lang('data_import'),
            'user_display_name' => $this->accounts->get_user_display_name($user_id),
            'privileges' => $this->roles_model->get_permissions_by_slug($role_slug),
        ]);

        $this->load->view('pages/import_data');
    }

    private function getSystemsList() {
        $systems[] = [
            'id' => 'SBK',
            'name' => 'SimplyBook.me',
            'data' => ['customers', 'schedule']
        ];

        // $systems[] = [
        //     'id' => 'TST',
        //     'name' => 'TestSystem',
        //     'data' => ['employees', 'schedule']
        // ];

        return $systems;
    }

    public function upload(): void
    {
        $user_id = session( 'user_id' );

        try {
            if (cannot( 'edit', PRIV_SYSTEM_SETTINGS)) {
                 if ( $user_id ) {
                    abort( 403, 'Forbidden' );
                }

                redirect( 'login' );

                return;
            }

            $return = [
                'success' => false,
            ];

            $system = request( 'source_system' );
            $type = request( 'type' );
            $data = request( 'data' );

            $method = $system['id'] . '_' . $type . '_upload';

            if (method_exists($this, $method)) {
                $ret = $this->$method( $data );
                $return['system'] = $system;
                $return['type'] = $type;
                $return = array_merge( $return, $ret );
            } else {
                throw new RuntimeException( 'No method ' . $method . ' defined' );
            }

            $return['success'] = true;

            try {
                $return = json_encode( $return, (JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE) );
            } catch (Throwable $je) {
                $return['success'] = false;
            }

            json_response( $return );

            
        } catch (Throwable $e) {
            json_exception( $e );
        }
    }
    
    protected function SBK_customers_upload(&$data): array
    {
        $return = [
            'skipped' => [],
            'failed' => [],
            'imported' => [],
        ];

        $lines = $this->dataToLines( $data );
        
        while (count($lines) > 0) {

            $values = array_shift( $lines );
            
            $doSave = false;

            if ( count( $values ) >= 6 ) {

                // Split name into first and last name
                $names = explode( ' ', $values[0], 2 );

                if ( count( $names ) == 1 ) {
                    // Last name is mandatory, so if not given use a space
                    $names[] = ' ';
                }

                // Remove strange first character from phone number
                if ( ord( substr( $values[2], 0, 1 ) ) == 39 ) {
                    $values[2] = substr( $values[2], 1 );
                }

                // 'Netherlands' is not a valid address
                if ( $values[3] == 'Netherlands' ) {
                    $values[3] = null;
                }

                $customer = [
                    'email' => $values[1],
                    'phone_number' => $values[2],
                    'first_name' => $names[0],
                    'last_name' => $names[1],
                    'address' => $values[3],
                    'timezone' => 'Europe/Amsterdam',
                    'custom_field_5' => 'Imported from SBK',
                    'language' => 'dutch',
                ];

                try {
                    $orgCust = $this->customers_model->find_by_email( $customer['email'] );
                    $doSave = $this->merge($orgCust['phone_number'], $customer['phone_number']) || $doSave;
                    $doSave = $this->merge($orgCust['first_name'], $customer['first_name']) || $doSave;
                    $doSave = $this->merge($orgCust['last_name'], $customer['last_name']) || $doSave;
                    $doSave = $this->merge($orgCust['address'], $customer['address']) || $doSave;

                    $customer = $orgCust;
                } catch (Throwable $e) {
                    // New customer, set ID to null and store it
                    $customer['id'] = null;
                    $doSave = true;
                }

                if ( $doSave ) {
                    try {
                        $this->customers_model->save( $customer );
                        $return['imported'][] = $customer;
                    } catch (LogicException $e) {
                        $return['failed'][] = $customer;
                    }

                } else {
                    $return['skipped'][] = $customer;
                }
            }
        }
        return $return;
    }

    protected function SBK_schedule_upload(&$data): array
    {
        $return = [
            'skipped' => [],
            'failed' => [],
            'imported' => [],
        ];

        $statusBooked = $this->booking_statusses_model->getDefault();
        $statusCancelled = $this->booking_statusses_model->get( [ 'name' => 'Geannuleerd' ] )[0];
        $lines = $this->dataToLines( $data );
        
        $roles = $this->roles_model->get(['slug' => DB_SLUG_PROVIDER]);

        if ((!is_array($roles)) || (count($roles) <> 1)) {
            throw new RuntimeException( 'Error fetching provider role' );
        }

        $providerRole = $roles[0]['id'];

        while ( count( $lines ) > 0 ) {
            $values = array_shift( $lines );
            $newCust = false;

            if ( count( $values ) >= 25 ) {
                try {
                    $times = explode( '-', $values[1], 2 );

                    try {
                        $custId = $this->customers_model->find_record_id( [ 'email' => $values[6] ] );
                    } catch (InvalidArgumentException $iae) {
                        // Customer doesn't exist yet. Create it
                        $names = explode( ' ', $values[5], 2 );

                        if ( count( $names ) == 1 ) {
                            // Last name is mandatory, so if not given use a space
                            $names[] = ' ';
                        }

                        // Remove strange first character from phone number
                        if ( ord( substr( $values[7], 0, 1 ) ) == 39 ) {
                            $values[7] = substr( $values[7], 1 );
                        }

                        $values[8] = ( $values[8] == 'Netherlands' ) ? null : $values[8];

                        $newCust = [
                            'email' => $values[6],
                            'phone_number' => $values[7],
                            'first_name' => $names[0],
                            'last_name' => $names[1],
                            'address' => $values[8],
                            'timezone' => 'Europe/Amsterdam',
                            'custom_field_5' => 'Imported from SBK',
                            'language' => 'dutch',
                        ];

                        try {
                            unset( $custId );
                            $custId = $this->customers_model->save( $newCust );
                            $newCust['id'] = $custId;
                            $return['customers'][] = $newCust;
                        } catch (Throwable $e) {
                            throw new FailImportException( "Failed to import new Customer", $e );
                        }
                    }
                
                    /**
                     * @todo Make sure the method of finding the correct service is correct
                     */
                    $appointment = [
                        'start_datetime' => $this->toSQLDateTime( trim( $values[0] ), $times[0] ),
                        'end_datetime' => $this->toSQLDateTime( trim( $values[0] ), $times[1] ),
                        //'id_users_customer' => $this->customers_model->find_record_id( ['email' => $values[6]] ),
                        'id_users_customer' => $custId,
                    ];
                    
                    $orgApp = $this->appointments_model->get([
                            'start_datetime' => $appointment['start_datetime'],
                            'end_datetime' => $appointment['end_datetime'],
                            'id_users_customer' => $appointment['id_users_customer'],
                        ], $limit = 1);

                    if (is_array($orgApp) & (count($orgApp) > 0)) {
                        throw new SkipImportException( 'Appointment already exists' );
                    }

                    
                    // New appointment, create full appointment
                    $subservices = [];
                    if (strlen($values[24]) > 0) {
                        $tmp = explode( ',', $values[24] );
                        foreach ($tmp as $s) {
                            $sName = trim(substr( $s, 0, strpos( $s, '(' ) ));
                            $s = $this->subservices_model->getSubserviceByName( $sName , true);
                            if ($s == 0) {
                                if ($newCust) {
                                    $this->customers_model->delete( $custId );
                                    unset($return['customers'][array_search($newCust, $return['customers'])]);
                                    $newCust = false;
                                }
                                throw new FailImportException("SubService '{$sName}' not found");
                            }
                            $subservices[] = $s;
                        }
                    }

                    if (count($subservices) > 0) {
                        $appointment['ids_subservices'] = $subservices;
                    }

                    $serviceId = $this->services_model->get( [ 'name' => $values[2] ] );
                    if ( (count($serviceId) == 0) | !isset($serviceId[0])) {
                        if ($newCust) {
                            $this->customers_model->delete( $custId );
                            unset($return['customers'][array_search($newCust, $return['customers'])]);
                            $newCust = false;
                        }
                        throw new FailImportException("Service '{$values[2]}' not found");
                    }

                    $appointment = array_merge( $appointment, [
                        'id' => null,
                        'id_users_provider' => $this->users_model->get( [ 'first_name' => $values[3], 'id_roles' => $providerRole ] )[0]['id'],
                        //'id_booking_statusses' => 1, // 1 = Booked
                        'id_booking_statusses' => ($values[13] == 'Nee') ? $statusBooked['id'] : $statusCancelled['id'],
                        'id_services' => $serviceId[0]['id'],
                        'location' => 'SBK',
                        'is_unavailability' => 0,
                    ] );



                    try {
                        $this->appointments_model->save( $appointment );
                    } catch (Throwable $e) {
                        if ($newCust) {
                            $this->customers_model->delete( $custId );
                            unset($return['customers'][array_search($newCust, $return['customers'])]);
                            $newCust = false;
                        }
                        throw new FailImportException( "Import of appoinment failed", $e );
                    }
                    
                    // Success!!! Add to imported array
                    $return['imported'][] = $appointment;
                } catch (FailImportException $fie) {
                    //$return['failed'][] = $appointment;
                    $return['failed'][] = $values;
                    $return['messages'][] = $fie->getMessage(); 
                } catch (SkipImportException $sie) {
                    $return['skipped'][] = $appointment;
                }
            }
        }

        return $return;
    }

    private function dataToLines(&$data): array
    {
        $file = base64_decode( $data['fileBase64'], false );
        $lines = explode( chr( 13 ), $file );

        // Remove the headers line
        array_shift( $lines );

        // parse each line to values array, and trim first value
        foreach ($lines as &$line) {
            $line = explode( chr( 9 ), $line );
            if (is_array($line) && (count($line) > 1) && is_string($line[0])) {
                $line[0] = trim( $line[0] );
            }
        }

        return $lines;
    }

    private function merge(&$org, &$new): bool
    {
        // They are the same -> no merge
        if ( ($org == $new))
            return false;

        // No new value -> no merge
        if ($new === null)
            return false;

        // Existing original value -> no merge
        if (!($org === null  || (is_string($org) && (strlen(trim($org)) == 0 ))))
            return false;

        // Copy new value to original value
        $org = $new;

        return true;
    }

    private function toSQLDateTime(String $date, string $time): string
    {
        $dateTime = DateTimeImmutable::createFromFormat( 'd-m-Y H:i', trim($date) . ' ' . trim($time) );

        return (!$dateTime)?null:$dateTime->format( 'Y-m-d H:i:s' );
    }
}