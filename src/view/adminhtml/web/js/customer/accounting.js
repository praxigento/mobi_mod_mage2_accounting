define([
    'jquery',
    'ko',
    'uiComponent',
    'uiRegistry',
    'Magento_Ui/js/modal/modal',
    'mage/template',
    'text!Praxigento_Accounting/templates/customer/accounting/slider.html'
], function ($, ko, Component, uiRegistry, Modal, mageTemplate, innerHtml) {

    /**
     * Collect working data into the local context (scope).
     */
    /* component constants */
    var TYPE_DIRECT = 'direct';
    /* pin globals into UI component scope */
    var baseUrl = BASE_URL;
    /* get current customer data from uiRegistry */
    var customerDs = uiRegistry.get('customer_form.customer_form_data_source');
    var customer = customerDs.data.customer;
    var customerId = customer.entity_id;
    /* define local working data */
    var urlOrigin = window.location.origin;
    var urlAdminBase = baseUrl.replace('/customer/', '/');
    var urlCustomerGet = urlAdminBase + 'prxgt/customer/get_byId/?form_key=' + FORM_KEY;
    ;
    var urlTransfer = urlOrigin + '/rest/all/V1/prxgt/account/asset/transfer';
    var urlAssetGet = urlOrigin + '/rest/all/V1/prxgt/account/asset/get';
    var urlCustomerSearch = urlOrigin + '/rest/all/V1/prxgt/customer/search/by_key';
    /* slider itself */
    var popup;
    /* View Model for slider */
    var viewModel = {
        amount: ko.observable(0),
        assets: undefined,
        counterparty: ko.observable(),
        customer: undefined,
        operationId: 0,
        selectedAsset: ko.observable(),
        selectedCounterparty: undefined,
        transferType: ko.observable(TYPE_DIRECT)
    };


    /**
     * Front-back communication functions
     */
    /* get initial data to fill in slider (customer info, assets balances, etc.) */
    var fnAjaxGetInitData = function () {
        /* flags for ajax requests - 'true' means that requests is done. */
        var isAjaxGetCustDone = false;
        var isAjaxGetAssetsDone = false;
        /* containers for customer & assets data loaded by AJAX */
        var customer, assets;

        /**
         * Definitions.
         */
        var options = {
            type: 'slide',
            responsive: true,
            innerScroll: true,
            clickableOverlay: true,
            title: $.mage.__('Accounting'),
            buttons: [{
                text: $.mage.__('Cancel'),
                class: '',
                click: function () {
                    this.closeModal();
                }
            }, {
                text: $.mage.__('Process'),
                class: '',
                click: fnAjaxProcessData
            }]
        };
        /* populate template with initial data then open slider */
        var fnModalOpen = function () {
            /* switch off loader */
            $('body').trigger('processStop');
            /* set parsed HTML as content for modal placeholder create modal slider */
            $('#modal_panel_placeholder').html(innerHtml);
            popup = Modal(options, $('#modal_panel_placeholder'));

            /* open modal slider, populate knockout view-model and bind it to template */
            popup.openModal();
            viewModel.amount = ko.observable(0);
            viewModel.assets = assets;
            viewModel.counterparty = ko.observable();
            viewModel.customer = customer;
            viewModel.operationId = 0;
            viewModel.transferType = ko.observable(TYPE_DIRECT);
            var elm = document.getElementById('modal_panel_placeholder');
            ko.cleanNode(elm);
            ko.applyBindings(viewModel, elm);

            /* add JQuery auto complete widget to the slider */
            $('#prxgtCustomerSearch').autocomplete({
                source: fnAjaxCustomerSearch,
                select: fnAutocompleteSelected,
                minLength: 2,
                /* disable auto-complete helper text */
                messages: {
                    noResults: '',
                    results: function () {
                    }
                }
            });
        }
        /* success responses handlers for 2 requests */
        var fnGetCustSuccess = function (response) {
            customer = response.data;
            isAjaxGetCustDone = true;
            if (isAjaxGetCustDone && isAjaxGetAssetsDone) {
                /* open slider */
                fnModalOpen();
            }
        }
        var fnGetAssetsSuccess = function (response) {
            assets = response.data.items;
            isAjaxGetAssetsDone = true;
            if (isAjaxGetCustDone && isAjaxGetAssetsDone) {
                /* open slider */
                fnModalOpen();
            }
        }

        /**
         * Processing
         */
        /* switch on ajax loader */
        $('body').trigger('processStart');

        /* compose and perform ajax request to get customer data */
        var request = {data: {customer_id: customerId}};
        var json = JSON.stringify(request);
        var opts = {
            data: json,
            contentType: 'application/json',
            type: 'post',
            success: fnGetCustSuccess
        };
        $.ajax(urlCustomerGet, opts);
        /* compose and perform ajax request to get assets data */
        request = {request: {data: {customerId: customerId}}};
        json = JSON.stringify(request);
        opts = {
            data: json,
            contentType: 'application/json',
            type: 'post',
            success: fnGetAssetsSuccess
        };
        $.ajax(urlAssetGet, opts);
    }

    /**
     * Search counterparty customers on the server and prepare data for UI.
     *
     * @param request
     * @param response
     */
    var fnAjaxCustomerSearch = function (request, response) {
        var data = {request: {data: {search_key: request.term}}};
        var json = JSON.stringify(data);
        $.ajax({
            url: urlCustomerSearch,
            data: json,
            contentType: 'application/json',
            type: 'post',
            success: function (resp) {
                /* convert API data into JQuery widget data */
                var data = resp.data;
                var found = [];
                for (var i in data.items) {
                    var one = data.items[i];
                    var nameFirst = one.name_first;
                    var nameLast = one.name_last;
                    var email = one.email;
                    var mlmId = one.mlm_id;
                    var label = nameFirst + ' ' + nameLast + ' <' + email + '> / ' + mlmId;
                    var foundOne = {
                        label: label,
                        value: label,
                        data: one
                    };
                    found.push(foundOne);
                }
                response(found);
            }
        });
    }

    var fnAjaxProcessData = function () {
        var asset = viewModel.selectedAsset();
        var assetId = asset.asset_id;
        var amount = viewModel.amount();
        var customerId = viewModel.customer.id;
        var counterPartyId = viewModel.selectedCounterparty;
        var type = viewModel.transferType();
        var isDirect = (type == TYPE_DIRECT);

        /* see: \Praxigento\Accounting\Controller\Adminhtml\Customer\Accounting\Process */
        var data = {
            amount: amount,
            assetId: assetId,
            counterPartyId: counterPartyId,
            customerId: customerId,
            isDirect: isDirect,
        };
        var json = JSON.stringify({request: {data: data}});

        /* process response from server: create modal slider and populate with data */
        var fnSuccess = function (response) {
            /* switch off ajax loader */
            $('body').trigger('processStop');
            viewModel.operationId = response.data.oper_id;
            var elm = document.getElementById('modal_panel_placeholder');
            ko.cleanNode(elm);
            ko.applyBindings(viewModel, elm);
            /* wait 3 sec. & close modal */
            setTimeout(function () {
                popup.closeModal();
            }, 3000);
        }

        var opts = {
            data: json,
            contentType: 'application/json',
            type: 'post',
            success: fnSuccess
        };

        $.ajax(urlTransfer, opts);
        /* switch on ajax loader */
        $('body').trigger('processStart');
    }

    var fnAutocompleteSelected = function (event, ui) {
        viewModel.selectedCounterparty = ui.item.data.id;
    }

    /* bind modal opening to 'Accounting' button on the form */
    /* (see \Praxigento\Accounting\Block\Customer\Adminhtml\Edit\AccountingButton) */
    $('#customer-edit-prxgt-accounting').on('click', fnAjaxGetInitData);

    /* this is required return to prevent Magento parsing errors */
    var result = Component.extend({});
    return result;
});