global.moment = require('moment');
require('bootstrap');
require('./adminModules/form.js');
require('./plugin/bootstrap-tagsinput');
require('./modules/global.js');
require('./plugin/jquery.validate.min.js');
import MaterialDateTimePicker from 'material-datetime-picker';

jQuery(function($){
    const $_ = {
        init() {
            this.initCache();
            this.initEstateChange();
            this.initTagsInput();
            this.initDatePicker();
            this.initCancelViewing();
            this.initEstateDelete();
            this.initEstateSync();
            this.initEstateUpdateCoordinates();
            this.initEstateRefreshFeed();
            this.initListingInfo();
        },

        initCache() {
            this.$jsWrap = $('.js-wrap');
            this.$estateChange = $('.js-estate-change');
            this.$estateDelete = $('.js-estate-delete');
            this.$estateSync = $('.js-estate-sync');
            this.$estateUpdateCoordinates = $('.js-estate-update-coordinates');
            this.$tagsInput = $('.js-tags-input');
            this.$datePicker = $('.js-date-picker');
            this.$datePickerBadge = $('.js-date-picker-badge');
            this.$datePickerText = $('.js-date-picker-text');
            this.$cancelViewing = $('.js-cancel-viewing');
            this.$feedRefresh = $('.js-feed-refresh');
            this.$listingInfo = $('.js-listing-info');
        },

        initCancelViewing() {
            $_.$cancelViewing.each((key, item) => {
                const
                    $currentButton = $(item),
                    $wrap = $currentButton.closest($_.$jsWrap),
                    path = $currentButton.data('cancel-path'),
                    viewingId = $currentButton.data('viewing-id');

                $currentButton.on('click', () => {
                    if (confirm('Are you sure you want to cancel this viewing?')) {
                        $.ajax({
                            url: path,
                            type: 'POST',
                            data: {
                                viewingId: viewingId
                            }
                        }).done(() => {
                            $wrap.css('pointer-events', 'none').fadeOut(500, () => {
                                $wrap.remove();
                            });
                        }).fail((error) => {
                            _errorHandler(error);
                        });
                    }
                });
            });
        },

        initDatePicker() {
            $_.$datePicker.each((key, item) => {
                const
                    $currentItem = $(item),
                    $badge = $currentItem.find($_.$datePickerBadge),
                    $text = $currentItem.find($_.$datePickerText),
                    updatePath = $currentItem.data('update-path'),
                    viewingId = $currentItem.data('viewing-id'),
                    selectedDate = new Date($text.text()),
                    selectedDateArray = [
                        selectedDate.getFullYear(),
                        selectedDate.getMonth(),
                        selectedDate.getDate(),
                        selectedDate.getHours(),
                        Math.round(selectedDate.getMinutes() / 5) * 5
                    ];

                const datepicker = new MaterialDateTimePicker({
                    el: $currentItem[0],
                    openedBy: 'click',
                    default: new Date(...selectedDateArray),
                    dateValidator: (date) => {
                        const
                            formatOptions = {
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric',
                                hour12: false,
                            },
                            addDate = Date.parse(new Intl.DateTimeFormat('en-US', formatOptions).format(date)),
                            now = Date.parse(new Intl.DateTimeFormat('en-US', formatOptions).format(new Date()));

                        return addDate >= now
                    }
                });

                $currentItem.on('click', () => {
                    datepicker.open();
                });

                datepicker.on('submit', (date) => {
                    const formatDate = new Intl.DateTimeFormat('en-US', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        hour: 'numeric',
                        minute: 'numeric',
                        hour12: false,
                    }).format(date);

                    $.ajax({
                        url: updatePath,
                        type: 'POST',
                        data: { date: formatDate, id: viewingId }
                    }).done(() => {
                        $badge.removeClass('bg-warning').addClass('bg-success');
                        $text.html(formatDate);
                    }).fail((error) => {
                        _errorHandler(error);
                    });
                });
            });
        },

        initEstateChange() {
            if (!$_.$estateChange.length) return false;

            $_.$estateChange.on('click', (e) => {
                const
                    $currentTarget = $(e.currentTarget),
                    dataUrl = $currentTarget.data('url'),
                    requestParameters = {
                        url: dataUrl,
                        type: 'POST',
                        dataType: 'json',
                    };
                $.ajax(requestParameters).done(() => {
                    $currentTarget.toggleClass('active');
                })
            });
        },

        initEstateDelete() {
            if (!$_.$estateDelete.length) return false;

            $_.$estateDelete.on('click', (e) => {
                const
                  $currentTarget = $(e.currentTarget),
                  dataUrl = $currentTarget.data('url'),
                  requestParameters = {
                      url: dataUrl,
                      type: 'POST',
                      dataType: 'json',
                  };
                $.ajax(requestParameters).done(() => {
                    $currentTarget.prop('disabled', true);
                })
            });
        },

        initEstateSync() {
            if (!$_.$estateSync.length) return false;

            $_.$estateSync.on('click', (e) => {
                const
                  $currentTarget = $(e.currentTarget),
                  dataId = $currentTarget.data('id'),
                  dataUrl = $currentTarget.data('url'),
                  requestParameters = {
                      url: dataUrl,
                      type: 'POST',
                      dataType: 'json',
                  };
                $currentTarget.prop('disabled', true);
                $(`#processing-status-${dataId}`).text('processing')
                $.ajax(requestParameters).done(() => {
                    console.log('done')
                }).fail((jq) => {
                    let data = jq.responseJSON;
                    alert(data.error)
                }).always(() => {
                    $(`#processing-status-${dataId}`).text('none')
                    $currentTarget.prop('disabled', false);
                })
            });
        },

        initEstateUpdateCoordinates() {
            if (!$_.$estateUpdateCoordinates.length) return false;

            $_.$estateUpdateCoordinates.on('click', (e) => {
                const
                  $currentTarget = $(e.currentTarget),
                  dataUrl = $currentTarget.data('url'),
                  requestParameters = {
                      url: dataUrl,
                      type: 'POST',
                      dataType: 'json',
                  };
                $currentTarget.prop('disabled', true);
                $.ajax(requestParameters).done(() => {
                    $currentTarget.prop('disabled', false);
                })
            });
        },

        initEstateRefreshFeed() {
            if (!$_.$feedRefresh.length) return false;

            $_.$feedRefresh.on('click', (e) => {
                const
                  $currentTarget = $(e.currentTarget),
                  dataName = $currentTarget.data('name'),
                  dataUrl = $currentTarget.data('url'),
                  requestParameters = {
                      url: dataUrl,
                      type: 'POST',
                      dataType: 'json',
                  };
                $currentTarget.prop('disabled', true);
                $.ajax(requestParameters).done((response) => {
                    $(`#${dataName}-feed-time`).text(response.lastRunTime);
                    $(`#${dataName}-feed-busy`).text(response.isBusy);
                    $currentTarget.prop('disabled', false);
                })
            });
        },

        initTagsInput() {
            if (!$_.$tagsInput.length) return false;

            $_.$tagsInput.tagsinput({
                tagClass: 'badge badge-primary',
                trimValue: true,
            });

            $_.$tagsInput.tagsinput('input').keypress((e) => {
                if (e.keyCode === 13) e.preventDefault();
            });
        },

        initListingInfo() {
            if (!$_.$listingInfo.length) return false;
            $_.$listingInfo.on('click', (e) => {
                e.preventDefault()

                const $currentTarget = $(e.currentTarget),
                  error = $currentTarget.data('error'),
                  trace = $currentTarget.data('trace');

                if (error) {
                    alert(`Error: ${error}\nTrace: ${trace}`)
                }
            });
        }
    };

    $(document).ready(() => {
        $_.init();
    });
});

