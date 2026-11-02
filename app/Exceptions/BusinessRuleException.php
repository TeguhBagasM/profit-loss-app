<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Pelanggaran aturan bisnis (mis. menghapus data yang masih dipakai).
 *
 * Pesan dari exception ini aman ditampilkan ke pengguna sebagai flash message.
 */
class BusinessRuleException extends RuntimeException
{
    //
}
