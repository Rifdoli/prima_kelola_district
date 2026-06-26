<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->after('user_id');
            $table->string('username')->unique()->after('name');
            $table->string('nik', 16)->nullable()->unique()->after('username');
            $table->string('phone_number', 20)->nullable()->after('email');

            $table->timestamp('last_update_password')->nullable()->after('password');
            $table->timestamp('last_login_at')->nullable()->after('last_update_password');
            $table->timestamp('password_valid_until')->nullable()->after('last_login_at');

            $table->boolean('is_ldap')->default(false)->after('password_valid_until');
            $table->boolean('is_active')->default(true)->after('is_ldap');
            $table->boolean('allow_be_login')->default(false)->after('is_active');

            // FK ke tabel organizations menyusul di issue terpisah (tabel belum ada).
            $table->unsignedBigInteger('organization_id')->nullable()->after('allow_be_login');

            $table->foreignId('parent_user_id')->nullable()->after('organization_id')
                ->constrained('users', 'user_id')->nullOnDelete();

            $table->string('photo')->nullable()->after('parent_user_id');
            $table->text('device_token')->nullable()->after('photo');
            $table->string('telegram_id')->nullable()->after('device_token');
            $table->string('mfa_type', 20)->nullable()->after('telegram_id');
            $table->timestamp('last_verify_mfa_at')->nullable()->after('mfa_type');
            $table->string('tag')->nullable()->after('last_verify_mfa_at');

            $table->foreignId('created_by')->nullable()->after('tag')
                ->constrained('users', 'user_id')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_at')
                ->constrained('users', 'user_id')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('updated_by');
            $table->dropConstrainedForeignId('created_by');
            $table->dropConstrainedForeignId('parent_user_id');

            $table->dropColumn([
                'uuid',
                'username',
                'nik',
                'phone_number',
                'last_update_password',
                'last_login_at',
                'password_valid_until',
                'is_ldap',
                'is_active',
                'allow_be_login',
                'organization_id',
                'photo',
                'device_token',
                'telegram_id',
                'mfa_type',
                'last_verify_mfa_at',
                'tag',
            ]);
        });
    }
};
