<?php

// IDE-only namespaced stubs for accounting module classes referenced via FQCN.

namespace System\Lib {
    if (!class_exists('System\\Lib\\IntelephenseServiceStub')) {
        class IntelephenseServiceStub
        {
            /**
             * @param string $name
             * @param array<int, mixed> $arguments
             * @return mixed
             */
            public function __call($name, $arguments)
            {
                return null;
            }
        }
    }

    if (!class_exists('System\\Lib\\JournalEntryService')) {
        class JournalEntryService extends IntelephenseServiceStub
        {
        }
    }

    if (!class_exists('System\\Lib\\AccountsReceivableService')) {
        class AccountsReceivableService extends IntelephenseServiceStub
        {
        }
    }

    if (!class_exists('System\\Lib\\AccountsPayableService')) {
        class AccountsPayableService extends IntelephenseServiceStub
        {
        }
    }

    if (!class_exists('System\\Lib\\CashBankService')) {
        class CashBankService extends IntelephenseServiceStub
        {
        }
    }

    if (!class_exists('System\\Lib\\TaxVATService')) {
        class TaxVATService extends IntelephenseServiceStub
        {
        }
    }

    if (!class_exists('System\\Lib\\FinancialReportService')) {
        class FinancialReportService extends IntelephenseServiceStub
        {
        }
    }

    if (!class_exists('System\\Lib\\AccountingControlsService')) {
        class AccountingControlsService extends IntelephenseServiceStub
        {
        }
    }
}

namespace System\Models {
    if (!class_exists('System\\Models\\GLAccount')) {
        class GLAccount extends \IntelephenseModelStub
        {
        }
    }

    if (!class_exists('System\\Models\\GLAccountBalance')) {
        class GLAccountBalance extends \IntelephenseModelStub
        {
        }
    }

    if (!class_exists('System\\Models\\JournalEntry')) {
        class JournalEntry extends \IntelephenseModelStub
        {
        }
    }

    if (!class_exists('System\\Models\\JournalItem')) {
        class JournalItem extends \IntelephenseModelStub
        {
        }
    }

    if (!class_exists('System\\Models\\ARLedger')) {
        class ARLedger extends \IntelephenseModelStub
        {
        }
    }

    if (!class_exists('System\\Models\\APLedger')) {
        class APLedger extends \IntelephenseModelStub
        {
        }
    }

    if (!class_exists('System\\Models\\BankAccount')) {
        class BankAccount extends \IntelephenseModelStub
        {
        }
    }

    if (!class_exists('System\\Models\\BankReconciliation')) {
        class BankReconciliation extends \IntelephenseModelStub
        {
        }
    }

    if (!class_exists('System\\Models\\AccountingPeriod')) {
        class AccountingPeriod extends \IntelephenseModelStub
        {
        }
    }

    if (!class_exists('System\\Models\\TaxCode')) {
        class TaxCode extends \IntelephenseModelStub
        {
        }
    }

    if (!class_exists('System\\Models\\AuditLog')) {
        class AuditLog extends \IntelephenseModelStub
        {
        }
    }

    if (!class_exists('System\\Models\\Customer')) {
        class Customer extends \IntelephenseModelStub
        {
        }
    }

    if (!class_exists('System\\Models\\Vendor')) {
        class Vendor extends \IntelephenseModelStub
        {
        }
    }
}
