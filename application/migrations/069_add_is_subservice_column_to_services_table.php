<?php defined('BASEPATH') or exit('No direct script access allowed');

/* ----------------------------------------------------------------------------
 * Easy!Appointments - Open Source Web Scheduler
 *
 * @package     EasyAppointments
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) 2013 - 2020, Alex Tselegidis
 * @license     http://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        http://easyappointments.org
 * @since       v1.4.0
 * ---------------------------------------------------------------------------- */

class Migration_Add_is_subservice_column_to_services_table extends EA_Migration
{
    /**
     * Upgrade method.
     */
    public function up(): void
    {
        if (!$this->db->field_exists('is_subservice', 'services')) {
            $fields = [
                'color' => [
                    'type' => 'TINYINT',
                    'constraint' => '4',
                    'default' => '0',
                    'after' => 'id_service_categories',
                ],
            ];

            $this->dbforge->add_column('services', $fields);

            $subQuery = $this->db
                ->distinct()
                ->select( 'subservice' )
                ->from( 'subservices' );
            $subSql = $subQuery->get_compiled_select();
            
            $this->db->where( key: 'ea_services.id not in (' . $subSql . ')' );
            
            $this->db->update('services', ['is_subservice' => 1]);
        }
    }

    /**
     * Downgrade method.
     */
    public function down(): void
    {
        if ($this->db->field_exists('is_subservice', 'services')) {
            $this->dbforge->drop_column('services', 'is_subservice');
        }
    }
}
