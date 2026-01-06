<?php

declare(strict_types=1);

namespace App\Constants;

/**
 * Chart of Accounts code constants.
 * Memudahkan maintenance dan mencegah typo pada account codes.
 */
final class AccountCodes
{
    // ASSETS (1xx)
    public const KAS = '111';
    public const PIUTANG_USAHA = '112';
    public const PERSEDIAAN_BAHAN = '113';

    // LIABILITIES (2xx)
    public const PENDAPATAN_DITERIMA_DIMUKA = '211';
    public const HUTANG_USAHA = '212';

    // EQUITY (3xx)
    public const MODAL_PEMILIK = '311';
    public const LABA_DITAHAN = '312';

    // REVENUE (4xx)
    public const PENDAPATAN_JASA_CETAK = '411';

    // EXPENSES (5xx)
    public const BEBAN_HPP = '511';
}
