<?php

use Phinx\Seed\AbstractSeed;

class InitialSeedData extends AbstractSeed {
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run() {
        $referenceDataPath = TRANQUIL_PATH_BASE."/resources/database/seeds/referenceData/";

        // Add reference data for locales from CSV
        $records = [];
        if (($handle = fopen(realpath($referenceDataPath."cd_locales.csv"), "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row = array(
                    'code' => $data[0],
                    'description' => $data[1],
                    'ordering' => $data[2],
                    'effectiveFrom' => date('Y-m-d H:i:s')
                );
                $records[] = $row;
            }
            fclose($handle);
        }
        $table = $this->table('cd_locales');
        $table->insert($records)->save();

        // Add reference data for timezones from CSV
        $records = [];
        if (($handle = fopen($referenceDataPath."cd_timezones.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row = array(
                    'code' => $data[0],
                    'description' => $data[1],
                    'daylightSavings'=> $data[2],
                    'ordering' => $data[3],
                    'effectiveFrom' => date('Y-m-d H:i:s')
                );
                $records[] = $row;
            }
            fclose($handle);
        }
        $table = $this->table('cd_timezones');
        $table->insert($records)->save();

        // Add reference data for countries from CSV
        $records = [];
        if (($handle = fopen($referenceDataPath."cd_countries.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row = array(
                    'code' => $data[0],
                    'description' => $data[1],
                    'ordering' => $data[2],
                    'effectiveFrom' => date('Y-m-d H:i:s')
                );
                $records[] = $row;
            }
            fclose($handle);
        }
        $table = $this->table('cd_countries');
        $table->insert($records)->save();

        // Add initial user data
        $records = [];
        if (($handle = fopen($referenceDataPath."entity.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row = array(
                    'id' => $data[0],
                    'version' => $data[1],
                    'type' => $data[2],
                    'subType' => $data[3],
                    'deleted' => $data[4],
                    'transactionId' => $data[5]
                );
                $records[] = $row;
            }
            fclose($handle);
        }
        $table = $this->table('entity');
        $table->insert($records)->save();

        $records = [];
        if (($handle = fopen($referenceDataPath."entity_users.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row = array(
                    'id' => $data[0],
                    'username' => $data[1],
                    'password' => $data[2],
                    'timezoneCode' => $data[3],
                    'localeCode' => $data[4],
                    'active' => $data[5],
                    'securityGroupId' => $data[6],
                    'registeredDateTime' => date('Y-m-d H:i:s')
                );
                $records[] = $row;
            }
            fclose($handle);
        }
        $table = $this->table('entity_users');  
        $table->insert($records)->save();

        // Generate OAuth Client record
        $records = [];
        $records[] = array(
            'clientId' => 'test_client',
            'clientSecret' => password_hash('password', PASSWORD_DEFAULT, ['cost' => 11])
        );
        $table = $this->table('sys_auth_clients');
        $table->insert($records)->save();
    }
}
