window._formatInputVal = (obj) => {
    const
        { val, dataFormatParams } = obj,
        {  formatCurrency, prefix='', suffix='', min=-Infinity, max=Infinity } = dataFormatParams,
        pureNum = _getPureNumber(val),
        fixVal = Math.min(Math.max(pureNum, min), max),
        finalVal = formatCurrency ? _formatCurrency(fixVal) : fixVal;

    return prefix + finalVal + suffix;
}

window._formatBigNum = (num) => {
    return num ? (
        num >= 1000000
            ? Math.ceil(num/100000) / 10 + 'm'
            : Math.ceil(num/100) / 10 + 'k'
    ) : num;
}

window._formatCurrencyCa = (str) => {
    return '$' + _formatCurrency(str);
}

window._formatCurrency = (str) => {
    return str.toString().replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1,');
}

window._getPureNumber = (str) => {
    return str.toString().replace(/[^0-9.]/g, "");
}

window._addZero = (num) => {
    return `${num < 10 ? '0' : ''}${num}`
}

window._getJsonFormData = (form, parseJson) => {
    return JSON.stringify(_getFormDataObj(form, parseJson));
}

window._getFormDataObj = (form, parseJson) => {
    const
        data = {},
        formData = $(form).serializeArray(),
        regExp = new RegExp('(?<arrayName>.*)\\[(?<criteriaName>.*)\]');

    formData.forEach(({ name, value='' }) => {
        if (name.indexOf('_ignore') === -1) {
            const setVal = parseJson ? _safeParseJson(value) : value;
            let checkArray =  regExp.exec(name);
            if (checkArray) {
                let {arrayName, criteriaName} = checkArray.groups;
                if (typeof data[arrayName] !== 'object' && arrayName !== 'propertyTypes') data[arrayName] = {};
                if (criteriaName) {
                    if (data[arrayName][criteriaName]) {
                        if (typeof data[arrayName][criteriaName] !== 'object') {
                            data[arrayName][criteriaName] = [data[arrayName][criteriaName]]
                        }
                        data[arrayName][criteriaName].push(value);
                    } else {
                        data[arrayName][criteriaName] = setVal;
                    }
                } else {
                    if (typeof data[arrayName] !== 'object') {
                        data[arrayName] = []
                    }
                    data[arrayName].push(value)
                }
            } else if (data[name]) {
                if (!Array.isArray(data[name])) data[name] = [data[name]];
                data[name].push(setVal);
            } else {
                data[name] = setVal;
            }
        }
    });

    return data;
}

window._safeParseJson = val => {
    try {
        console.log(val, 'val', JSON.parse(val), 'jsonParse');
        return JSON.parse(val);
    } catch (e) {
        return val
    }
}

window._getBpVal = (array=[], breakpoints=[1300, 1000, 700, 400]) => {
    const currentWidth = window.outerWidth;
    let relatedVal = array[0];

    for (let i = 0; i < breakpoints.length; i++) {
        if ((currentWidth <= breakpoints[i]) && array[i+1]) relatedVal = array[i+1];
    }

    return relatedVal;
}

window._authenticationRequiredRequest = (props) => {
    const { customAuthenticatedRequest, requestParameters, callback } = props;

    if (IS_AUTHENTICATED_REMEMBERED) {
        if (customAuthenticatedRequest) {
            customAuthenticatedRequest();
        } else {
            _sendAjax({ requestParameters, callback });
        }
    } else {
        $('body').trigger('trigger:open-popup', {
            target: 'authorization',
            show_overlay: true,
        });

        if (requestParameters) {
            sessionStorage.setItem('extra_request', JSON.stringify({
                requestParameters,
                reload: true,
            }));
        }
    }
}

window._sendAjax = (props) => {
    const { requestParameters, reload, callback } = props;

    $.ajax(_safeParseJson(requestParameters)).done(() => {
        if (callback) callback();
        if (reload) window.location.reload();
    })
    .fail((error) => {
        _errorHandler(error);
    });
}

window._errorHandler = (response) => {
    const
        { responseJSON={} } = response,
        { error, errors } = responseJSON;

    if (error || errors) alert(error || errors.join('\n'));
}
