<?php defined('BASEPATH') or exit('No direct script access allowed');

/* ----------------------------------------------------------------------------
 * Easy!Appointments - Online Appointment Scheduler
 *
 * @package     EasyAppointments
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) Alex Tselegidis
 * @license     https://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        https://easyappointments.org
 * @since       v1.4.0
 * ---------------------------------------------------------------------------- */

class Migration_Improve_Booking_Status_Fields extends EA_Migration 
{
    /**
     * Upgrade method.
     */
	public function up(): void {
		if ( ! $this->db->table_exists( 'booking_statusses' ) ) {
            $this->dbforge->add_field([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'auto_increment' => true,
                ],
                'create_datetime' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'update_datetime' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'name' => [
                    'type' => 'VARCHAR',
                    'constraint' => '128',
                    'null' => false,
                ],
                'is_busy' => [
                    'type' => 'TINYINT',
                    'constraint' => '4',
                    'default' => '1',
                ],
                'description' => [
                    'type' => 'VARCHAR',
                    'constraint' => '256',
                    'null' => true,
                ],
            ]);
            $this->dbforge->add_key('id', true);
            $this->dbforge->add_key('name');

            $this->dbforge->create_table('booking_statusses', true, ['engine' => 'InnoDB']);
		}

        if (!$this->db->field_exists('is_busy', 'appointments')) {
            $fields = [
                'is_busy' => [
                    'type' => 'TINYINT',
                    'constraint' => '4',
                    'default' => '1',
                    'after' => 'total_price',
                ],
            ];

            $this->dbforge->add_column('appointments', $fields);
        }
	}

    /**
     * Downgrade method.
     */
	public function down(): void {
        if ($this->db->field_exists('is_busy', 'appointments')) {
            $this->dbforge->drop_column('appointments', 'is_busy');
        }

        if ($this->db->table_exists('booking_statusses')) {
            $this->dbforge->drop_table('booking_statusses');
        }
	}

}