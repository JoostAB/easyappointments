<?php defined('BASEPATH') or exit('No direct script access allowed');

/* ----------------------------------------------------------------------------
 * Easy!Appointments - Online Appointment Scheduler
 *
 * @package     EasyAppointments
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) Alex Tselegidis
 * @license     https://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        https://easyappointments.org
 * @since       v1.6.0
 * ---------------------------------------------------------------------------- */

/**
 * Appointments model.
 *
 * @package Models
 */
class Booking_Statusses_model extends EA_Model {
    /**
     * @var array
     */
    protected array $casts = [
        'id' => 'integer',
        'is_busy' => 'boolean',
    ];

    /**
     * @var array
     */
    protected array $api_resource = [
        'id' => 'id',
        'name' => 'name',
        'isBusy' => 'is_busy',
        'description' => 'description',
    ];

    private array $true_values = ["true", "1", "yes"];

    public function query(): CI_DB_query_builder
    {
        return $this->db->from('booking_statusses');
    }

    public function get() {
        $statusses = $this->db->get('booking_statusses')->result_array();

        foreach ($statusses as &$status) {
            $this->cast($status);
        }

        return $statusses;
    }

    protected function insert(array $status): int
    {
        $status['create_datetime'] = date('Y-m-d H:i:s');
        $status['update_datetime'] = date('Y-m-d H:i:s');

        if (!$this->db->insert('booking_statusses', $status)) {
            throw new RuntimeException('Could not insert status.');
        }

        return $this->db->insert_id();
    }

    protected function update(array $status): int
    {
        $status['update_datetime'] = date('Y-m-d H:i:s');

        if (!$this->db->update('booking_statusses', $status, ['id' => $status['id']])) {
            throw new RuntimeException('Could not update status.');
        }

        return $status['id'];
    }

    public function validate(array $status): void
    {
        // If a status ID is provided then check whether the record really exists in the database.
        if (!empty($status['id'])) {
            $count = $this->db->get_where('booking_statusses', ['id' => $status['id']])->num_rows();

            if (!$count) {
                throw new InvalidArgumentException(
                    'The provided booking_status ID does not exist in the database: ' . $status['id'],
                );
            }
        }

        // Make sure all required fields are provided.
        if (empty($status['name'])) {
            throw new InvalidArgumentException('Not all required fields are provided: ' . print_r($status, true));
        }
    }

    public function save(array $status): int
    {
        if ($status['id'] == 'null') {
            unset($status['id']);
        }

        $status['is_busy'] = ( array_search( strtolower($status['is_busy']), $this->true_values ) === false ) 
            ? '0' 
            : '1';

        $this->validate($status);

        if (empty($status['id'])) {
            return $this->insert($status);
        } else {
            return $this->update($status);
        }
    }

    public function delete(int $id): void {
        $this->db->delete('booking_statusses', ['id' => $id]);
    }
}