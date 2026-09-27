{literal}
(function ($) {
    var vatTreatmentHelp = {
        standard: 'Supplier VAT No. and Supplier Invoice No. are required when VAT is claimed.',
        zero: 'Zero-rated supply: no VAT.',
        exempt: 'Exempt / out-of-scope supply (e.g. salaries, government fees, rent of residential property): no VAT.',
        'import': 'Enter the VAT paid at customs and the customs declaration number.',
        reverse_charge: 'Foreign supplier charges no VAT; 15% is self-assessed as output and input VAT.'
    };

    function parseNumber(value) {
        value = String(value || '').replace(/[^0-9.,-]/g, '');
        if (value.indexOf(',') > -1 && value.indexOf('.') > -1) {
            value = value.replace(/,/g, '');
        } else {
            value = value.replace(',', '.');
        }
        var n = parseFloat(value);
        return isNaN(n) ? 0 : n;
    }

    function applyExpenseVatTreatment() {
        var treatment = $('#vat_treatment').val();
        var noVat = treatment === 'zero' || treatment === 'exempt' || treatment === 'reverse_charge';

        $('#vat_treatment_help').text(vatTreatmentHelp[treatment] || '');
        $('#supplier_invoice_number_label').text(treatment === 'import' ? 'Customs Declaration No.' : 'Supplier Invoice No.');
        $('#vat_amount_group').toggle(!noVat);

        if (noVat) {
            $('#vat_amount').val('');
        }
    }

    $(document).on('change', '#vat_treatment', applyExpenseVatTreatment);

    $(document).on('click', '#vat_calc_15', function (e) {
        e.preventDefault();
        var gross = parseNumber($('#amount').val());
        $('#vat_amount').val(gross > 0 ? (gross * 15 / 115).toFixed(2) : '');
    });

    $(document).on('change', '#payee', function () {
        var cid = $(this).val();
        var $vat = $('#supplier_vat_number');
        if (!cid) {
            return;
        }
        $.getJSON($('#_url').val() + 'purchases/supplier-vat/' + cid + '/', function (data) {
            if (data && data.vat_number) {
                $vat.val(data.vat_number);
            }
        });
    });

    applyExpenseVatTreatment();
})(jQuery);
{/literal}
