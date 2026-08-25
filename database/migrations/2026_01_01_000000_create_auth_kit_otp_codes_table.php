<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auth_kit_otp_codes', function (Blueprint $table): void {
            $table->id();
            $table->string('identifier')->index(); // email/phone/user key
            $table->string('code_hash');
            $table->unsignedInteger('expires_at'); // unix timestamp
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();

            $table->index(['identifier', 'consumed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_kit_otp_codes');
    }
};
