define([
    "Praxigento_Core/js/grid/column/link"
], function (Column) {
    "use strict";

    return Column.extend({
        defaults: {
            /** @see \Praxigento\Accounting\Ui\DataProvider\Grid\Transaction\Query::A_CREDIT_CUST_ID */
            idAttrName: "creditCustId",
            route: "/customer/index/edit/id/"
        }
    });
});
