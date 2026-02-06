<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Users Table Updates
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('user')->after('password'); // admin or user
            }
            if (!Schema::hasColumn('users', 'status')) {
                $table->string('status')->default('active')->after('role'); // active or inactive
            }
            if (!Schema::hasColumn('users', 'notes')) {
                $table->text('notes')->nullable()->after('status');
            }
        });

        // Practices Table Updates
        Schema::table('practices', function (Blueprint $table) {
            if (!Schema::hasColumn('practices', 'year')) {
                $table->integer('year')->nullable()->after('id');
            }
            if (!Schema::hasColumn('practices', 'month')) {
                $table->integer('month')->nullable()->after('year');
            }
            if (!Schema::hasColumn('practices', 'client_id')) {
                $table->string('client_id')->nullable()->after('title'); // String for simplicity or ID if rel.
            }
            // Notes already might exist, check? We assume description or similar.
            if (!Schema::hasColumn('practices', 'notes')) {
                $table->text('notes')->nullable();
            }
        });

        // Practice Relations Table (Many-to-Many self reference)
        if (!Schema::hasTable('practice_relations')) {
            Schema::create('practice_relations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('source_practice_id')->constrained('practices')->onDelete('cascade');
                $table->foreignId('related_practice_id')->constrained('practices')->onDelete('cascade');
                $table->string('relation_type')->default('related'); // e.g., 'related', 'parent', 'child'
                $table->timestamps();

                $table->unique(['source_practice_id', 'related_practice_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('practice_relations');

        Schema::table('practices', function (Blueprint $table) {
            $table->dropColumn(['year', 'month', 'client_id', 'notes']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'status', 'notes']);
        });
    }
};
