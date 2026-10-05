<?php defined('BASEPATH') or exit('No direct script access allowed');

class Import_data extends EA_Controller
{
    public function __construct() {
        parent::__construct();

        
    }

    public function index(): void {
        session( [ 'dest_url' => site_url( 'import_data' ) ] );

        $user_id = session( 'user_id' );

        if ( cannot( 'view', PRIV_SYSTEM_SETTINGS ) ) {
            if ( $user_id ) {
                abort( 403, 'Forbidden' );
            }

            redirect( 'login' );

            return;
        }

        script_vars( [
            'import_systems' => $this->getSystemsList(),
        ] );

        $this->load->view('pages/import_data');
    }

    private function getSystemsList() {
        $systems[] = [
            'id' => 'SBK',
            'name' => 'SimplyBook.me',
            'data' => ['customers', 'schedule']
        ];

        $systems[] = [
            'id' => 'TST',
            'name' => 'TestSystem',
            'data' => ['employees', 'schedule']
        ];

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

            $system = request( 'source_system' );
            $type = request( 'type' );
            $data = request( 'data' );

            /*
            $tmp = base64_decode( $data['fileBase64'], false );

            
            $lines = explode( chr( 13 ), $tmp );

            array_shift( $lines );

            while (count($lines) > 0) {
                $line = array_shift( $lines );
                $values = explode( chr( 9 ), $line );
                $tmp = $values[0];
            }
            */

            $method = $system['id'] . '_' . $type . '_upload';

            if (method_exists($this, $method)) {
                $this->$method( $data );
            } else {
                throw new RuntimeException( 'No method ' . $method . ' defined' );
            }
            $return[] = [
                'result' => 'ok'
            ];

            
            json_response( $return );
        } catch (Throwable $e) {
            json_exception( $e );
        }
    }
    
    function SBK_customers_upload(&$data): void
    {
        $this->load->model('customers_model');

        $lines = explode( chr( 13 ), base64_decode( $data['fileBase64'], false ) );

        // Remove the headers line
        array_shift( $lines );

        while (count($lines) > 0) {
            $line = array_shift( $lines );
            $values = explode( chr( 9 ), $line );
            
            $email = $values[1];

            /*
            $custId = $this->customers_model->find_record_id([
                'email' => $email
            ]);
            */
            //if (!$custId) {
            if (!$this->customers_model->exists(['email' => $email])) {
                $names = trim( substr( $values[0], 1 ) );
                $names = explode(' ', $names, 2);
                $customer = [
                    'email' => $email,
                    'phone_number' => str_starts_with("'",$values[1])?substr($values[1],1):$values[1],
                    'first_name' => $names[0],
                    'last_name' => (count($names) > 1)?$names[1]:NULL,
                    'address' => $values[2],
                    'timezone' => 'Europe/Amsterdam',
                    'custom_field_5' => 'Imported from SBK',
                ];

                if (true) {
                    $this->customers_model->save($customer);  
                }
            }

        }

        
    }
}