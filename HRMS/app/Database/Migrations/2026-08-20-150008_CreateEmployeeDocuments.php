<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** file_path is a writable/ relative path, never a public one — served only by EmployeeDocumentsController::download() behind a permission check. */
class CreateEmployeeDocuments extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'employee_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'document_type'     => [
                'type'       => 'ENUM',
                'constraint' => [
                    'aadhaar', 'pan', 'passport', 'driving_license', 'resume', 'appointment_letter',
                    'offer_letter', 'education_certificate', 'experience_certificate', 'other',
                ],
            ],
            'document_number'   => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'file_path'         => ['type' => 'VARCHAR', 'constraint' => 255],
            'original_filename' => ['type' => 'VARCHAR', 'constraint' => 255],
            'mime_type'         => ['type' => 'VARCHAR', 'constraint' => 100],
            'file_size'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'expiry_date'       => ['type' => 'DATE', 'null' => true],
            'status'            => ['type' => 'ENUM', 'constraint' => ['pending', 'verified', 'rejected'], 'default' => 'pending'],
            'remarks'           => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'uploaded_by'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('employee_id');
        $this->forge->addKey('document_type');
        $this->forge->addForeignKey('employee_id', 'employees', 'id', '', 'CASCADE');
        $this->forge->createTable('employee_documents');
    }

    public function down()
    {
        $this->forge->dropTable('employee_documents');
    }
}
