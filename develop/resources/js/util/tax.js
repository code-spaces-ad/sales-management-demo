
/**
 * 金額端数処理
 *
 * @param method
 * @param value
 * @returns {number}
 */
window.calcAmountRounding = function (method, value) {
    // 切り捨て
    if (method === window.Laravel.enums.rounding_method_type.round_down) {
        return Math.floor(value);
    }
    // 切り上げ
    if (method === window.Laravel.enums.rounding_method_type.round_up) {
        return Math.ceil(value);
    }
    // 四捨五入
    if (method === window.Laravel.enums.rounding_method_type.round_off) {
        return Math.round(value);
    }
    // 上記以外は「切り捨て」で返す
    //console.warn('金額端数処理-端数指定なし：「切り捨て」で処理');
    return Math.floor(value);
}

/**
 * 税額計算+端数処理
 *
 * @param taxRate
 * @param method
 * @param value
 * @returns {number}
 */
window.calcTaxRounding = function (taxRate, method, value) {
    let useFlag = $('#tax_calc_use_flag').val();
    if (useFlag && taxRate > 0) {
        // 切り捨て
        if (method === window.Laravel.enums.rounding_method_type.round_down) {
            return Math.floor(value * (taxRate / 100));
        }
        // 切り上げ
        if (method === window.Laravel.enums.rounding_method_type.round_up) {
            return Math.ceil(value * (taxRate / 100));
        }
        // 四捨五入
        if (method === window.Laravel.enums.rounding_method_type.round_off) {
            return Math.round(value * (taxRate / 100));
        }
        // 切り捨て
        //console.warn('税額端数処理-端数指定なし：「切り捨て」で処理')
        return Math.floor(value * (taxRate / 100));
    }
    return 0;
}

/**
 * 内税額計算(切捨て固定)
 *
 * @param taxRate
 * @param value
 * @returns {number}
 */
window.calcInTax = function (taxRate, value) {
    // 切り捨て
    return value - Math.floor((value / Math.floor((1 + (taxRate / 100)) * 100)) * 100);
}
window.calcTaxIn = function (taxRate, value) {
    // 切り捨て
  return Math.round(value - (value / (1 + (taxRate / 100)) )) ;
}
window.calcTaxOut = function (taxRate, value) {
    // 切り捨て
    return Math.round(value * (taxRate / 100));
}

/**
 * 税計算
 *
 * @param taxCalcTypeId
 * @param taxTypeId
 * @param taxRate
 * @param reducedTaxFlag
 * @param taxRoundingMethod
 * @param subTotal
 * @returns {*}
 */
window.calcTax = function (taxCalcTypeId, taxTypeId, taxRate, reducedTaxFlag, taxRoundingMethod, subTotal) {
    let total = {
        'tax': 0,               // 外税額
        'inTax': 0,             // 内税額
        'consumption': 0,       // 通常税率 - 外税対象税抜額
        'consumptionTax': 0,    // 通常税率 - 外税額
        'consumptionIn': 0,     // 通常税率 - 内税対象税抜額
        'consumptionInTax': 0,  // 通常税率 - 内税額
        'reduced': 0,           // 軽減税率 - 外税対象税抜額
        'reducedTax': 0,        // 軽減税率 - 外税額
        'reducedIn': 0,         // 軽減税率 - 内税対象税抜額
        'reducedInTax': 0,      // 軽減税率 - 内税額
        'notax': 0,             // 非課税対象額
    };

    let tax = 0;
    if (taxRate > 0 && taxTypeId === window.Laravel.enums.tax_type.out_tax) {
        // 外税
        tax = calcTaxRounding(taxRate, taxRoundingMethod, subTotal);
        total['tax'] = tax;
    }
    if (taxRate > 0 && taxTypeId === window.Laravel.enums.tax_type.in_tax) {
        // 内税
        tax = calcInTax(taxRate, subTotal);
        total['inTax'] = tax;
    }
    if (taxRate === 0) {
        total['notax'] = subTotal;
    } else {
        if (reducedTaxFlag === window.Laravel.enums.reduced_tax_flag_type.reduced) {
            // 軽減税率
            if (taxTypeId === window.Laravel.enums.tax_type.out_tax) {
                // 外税
                total['reduced'] = subTotal;
                total['reducedTax'] = tax;
            }
            if (taxTypeId === window.Laravel.enums.tax_type.in_tax) {
                // 内税
                total['reducedIn'] = subTotal;
                total['reducedInTax'] = tax;
            }
        } else {
            // 通常税率
            if (taxTypeId === window.Laravel.enums.tax_type.out_tax) {
                // 外税
                total['consumption'] = subTotal;
                total['consumptionTax'] = tax;
            }
            if (taxTypeId === window.Laravel.enums.tax_type.in_tax) {
                // 内税
                total['consumptionIn'] = subTotal;
                total['consumptionInTax'] = tax;
            }
        }
    }

    return total;
}

/**
 * 伝票単位の税計算
 *
 * @param total
 * @param taxCalcTypeId
 * @param groupTotal
 * @returns {*}
 */
window.calcOrderTax = function (total, taxCalcTypeId, groupTotal) {
    for (let key in groupTotal) {
        // グループキー単位の税計算
        let row_total = calcTax(taxCalcTypeId,
            parseInt(key.split('-')[0]), parseInt(key.split('-')[1]), parseInt(key.split('-')[2]), parseInt(key.split('-')[3]),
            groupTotal[key]
        );
        total['taxTotal'] += row_total['tax'];
        total['inTaxTotal'] += row_total['inTax'];
        total['consumptionTotal'] += row_total['consumption'];
        total['consumptionTaxTotal'] += row_total['consumptionTax'];
        total['consumptionInTotal'] += row_total['consumptionIn'];
        total['consumptionInTaxTotal'] += row_total['consumptionInTax'];
        total['reducedTotal'] += row_total['reduced'];
        total['reducedTaxTotal'] += row_total['reducedTax'];
        total['reducedInTotal'] += row_total['reducedIn'];
        total['reducedInTaxTotal'] += row_total['reducedInTax'];
        total['notaxTotal'] += row_total['notax'];
    }
    return total;
}

/**
 * 値引按分
 *
 * @param discount
 * @param amount
 * @param subTotal
 * @returns {number}
 */
window.calcChunkDiscount = function (discount, amount, subTotal) {
    return Math.round((discount * amount) / subTotal);
}

/**
 * 各グループに値引きの按分
 * グループメンバー( consumption = 10%_TaxExcluded, consumptionIn = 10%_TaxIncluded, reduced = 8%_TaxExcluded, reducedIn = 8%_TaxIncluded, noTax )
 *
 * @param total
 * @param individualGroupDiscount
 * @param subTotal
 * @param discount
 */
window.calcIndividualGroupDiscount = function (total, individualGroupDiscount, subTotal, discount) {
    let getLastAvailableProductTaxType = 0;

    if (discount === 0 || !subTotal > 0) {
        return;
    }

    if (!isNaN(total['consumptionTotal']) && total['consumptionTotal'] > 0) {
        getLastAvailableProductTaxType = 1;
        individualGroupDiscount['consumptionTotalDiscount'] = calcChunkDiscount(discount, total['consumptionTotal'], subTotal);
    }
    if (!isNaN(total['consumptionInTotal']) && total['consumptionInTotal'] > 0) {
        getLastAvailableProductTaxType = 2;
        individualGroupDiscount['consumptionInTotalDiscount'] = calcChunkDiscount(discount, total['consumptionInTotal'], subTotal);
    }
    if (!isNaN(total['reducedInTotal']) && total['reducedInTotal'] > 0) {
        getLastAvailableProductTaxType = 3;
        individualGroupDiscount['reducedInTotalDiscount'] = calcChunkDiscount(discount, total['reducedInTotal'], subTotal);
    }
    if (!isNaN(total['reducedTotal']) && total['reducedTotal'] > 0) {
        getLastAvailableProductTaxType = 4;
        individualGroupDiscount['reducedTotalDiscount'] = calcChunkDiscount(discount, total['reducedTotal'], subTotal);
    }
    if (!isNaN(total['notaxTotal']) && total['notaxTotal'] > 0) {
        getLastAvailableProductTaxType = 5;
        individualGroupDiscount['notaxTotalDiscount'] = calcChunkDiscount(discount, total['notaxTotal'], subTotal);
    }

    /* diffOfDiscount = discount - SumOf(individualGroupDiscount) */
    let diffOfDiscount = discount - (individualGroupDiscount['consumptionTotalDiscount'] +
        individualGroupDiscount['consumptionInTotalDiscount'] +
        individualGroupDiscount['reducedInTotalDiscount'] +
        individualGroupDiscount['reducedTotalDiscount'] +
        individualGroupDiscount['notaxTotalDiscount']
    );

    if (diffOfDiscount === 0) {
        return;
    }

    /*　最後既存商品グループに値引き額調整　*/

    if (getLastAvailableProductTaxType === 1) {
        individualGroupDiscount['consumptionTotalDiscount'] += diffOfDiscount;
        return;
    }
    if (getLastAvailableProductTaxType === 2) {
        individualGroupDiscount['consumptionInTotalDiscount'] += diffOfDiscount;
        return;
    }
    if (getLastAvailableProductTaxType === 3) {
        individualGroupDiscount['reducedInTotalDiscount'] += diffOfDiscount;
        return;
    }
    if (getLastAvailableProductTaxType === 4) {
        individualGroupDiscount['reducedTotalDiscount'] += diffOfDiscount;
        return;
    }
    if (getLastAvailableProductTaxType === 5) {
        individualGroupDiscount['notaxTotalDiscount'] += diffOfDiscount;
    }
}

/**
 * 値引を差し引く
 *
 * @param total
 * @param individualGroupDiscount
 */
window.calcIndividualGroupTotalAfterDiscount = function (total, individualGroupDiscount) {

    /* 個別金額計(小計), 10%, [税抜] */
    if (!isNaN(total['consumptionTotal'])) {
        total['consumptionTotal'] -= individualGroupDiscount['consumptionTotalDiscount'];
    }
    /* 個別金額計(小計), 10%, [税込] */
    if (!isNaN(total['consumptionInTotal'])) {
        total['consumptionInTotal'] -= individualGroupDiscount['consumptionInTotalDiscount'];
    }
    /* 個別金額計(小計), 8%, [税抜] */
    if (!isNaN(total['reducedInTotal'])) {
        total['reducedInTotal'] -= individualGroupDiscount['reducedInTotalDiscount'];
    }
    /* 個別金額計(小計), 8%, [税込] */
    if (!isNaN(total['reducedTotal'])) {
        total['reducedTotal'] -= individualGroupDiscount['reducedTotalDiscount'];
    }
    /* 個別金額計(小計), [非課税] */
    if (!isNaN(total['notaxTotal'])) {
        total['notaxTotal'] -= individualGroupDiscount['notaxTotalDiscount'];
    }
}

/**
 * 税金の計算
 * ※ CodeSpacesでは使用しない
 *
 * @param total
 * @param method
 */
window.calcIndividualGroupTotalTax = function (total, method) {

    let reducedValue = 8;
    let consumptionValue = 10;

    total['reducedTaxTotal'] = calcTaxRounding(reducedValue, method, total['reducedTotal']);
    total['reducedInTaxTotal'] = calcTaxIn(reducedValue, total['reducedInTotal']);
    total['consumptionTaxTotal'] = calcTaxRounding(consumptionValue, method, total['consumptionTotal']);
    total['consumptionInTaxTotal'] = calcTaxIn(consumptionValue, total['consumptionInTotal']);
}

/**
 * 対象日付の税率を取得
 */
window.getTargetDateTaxRate = function (date) {
    $.ajax({
        url: "/ajax/get_tax_rate",
        type: "GET",
        data: {
            date: date.replace(/-/g, "/"),
        },
    }).done(function(data, textStatus, jqXHR) {
        $('#tax_rate').val(data.normal_tax_rate);
        $('#reduced_tax_rate').val(data.reduced_tax_rate);
    }).fail(function(jqXHR, textStatus, errorThrown) {
        console.log('Error!!! : ' + jqXHR.status + ' : ' + textStatus);
        alert('税率の取得に失敗しました');
    });
}
