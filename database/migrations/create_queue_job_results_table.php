<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queue_job_results', function (Blueprint $table) {

            $table->uuid('id')
                ->primary();


            /**
             * Laravel queue job identifier.
             */
            $table->string('job_id')
                ->nullable()
                ->index();


            /**
             * Job class name.
             *
             * Example:
             * SchoolPalm\Timetable\Jobs\GenerateTimetableJob
             */
            $table->string('job_class')
                ->index();


            /**
             * School context inside the tenant.
             */
            $table->unsignedBigInteger('school_id')
                ->nullable()
                ->index();


            /**
             * User who initiated the job.
             */
            $table->unsignedBigInteger('user_id')
                ->nullable()
                ->index();


            /**
             * Module owning the job.
             *
             * Example:
             * timetable
             * reports
             * admissions
             */
            $table->string('module')
                ->nullable()
                ->index();


            /**
             * Job lifecycle.
             *
             * pending
             * processing
             * completed
             * failed
             */
            $table->string('status')
                ->default('pending')
                ->index();


            /**
             * Successful job output.
             */
            $table->json('result')
                ->nullable();


            /**
             * Error details.
             */
            $table->json('error')
                ->nullable();


            $table->timestamp('started_at')
                ->nullable();


            $table->timestamp('completed_at')
                ->nullable();


            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('queue_job_results');
    }
};
