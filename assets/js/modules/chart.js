jQuery(function($){
    const $_ = {
        init(){
            this.initCache();
            this.initChart();
        },

        initCache() {
            this.$chart = $('.js-chart');
            this.$wrap = $('.js-chart-wrap');
            this.$form = $('.js-chart-form');
            this.$preloaderWrap = $('.js-preloader-wrap');
            this.$scrollContainer = $('.js-chart-scroll-container');

            this.colorMods = ['_color-a', '_color-b', '_color-c', '_color-d', '_color-e'];
        },

        initChart() {
            $_.$chart.each((key, item) => {
                const
                    $currentChart = $(item),
                    $relatedWrap = $currentChart.closest($_.$wrap),
                    $relatedForm = $relatedWrap.find($_.$form),
                    $relatedPreloaderWrap = $relatedWrap.find($_.$preloaderWrap),
                    $relatedScrollContainer = $relatedWrap.find($_.$scrollContainer),
                    dataParams = $currentChart.data('params'),
                    { path, fitLabelsFromCount } = dataParams,
                    id = `line-chart-${key}`;

                let chart = null;

                $_._loadData({ path, $relatedForm, $relatedPreloaderWrap }).done((data) => {
                    if (!data.labels.length) {
                        $_.$wrap.hide()
                        return false;
                    }

                    chart = $_._constructChart({
                        id,
                        fitLabelsFromCount,
                        $currentChart,
                        ...$_._parseData(data)
                    });

                    $_._bindFormChangeEvent({
                        $relatedPreloaderWrap,
                        $currentChart,
                        $relatedForm,
                        chart,
                        path
                    });

                    $relatedScrollContainer.on('scroll', () => {
                        $_._clearTooltips($currentChart);
                    });
                });
            });
        },

        _loadData(props) {
            const
                startTime = $.now(),
                { path, $relatedForm, $relatedPreloaderWrap } = props,
                formData = _getFormDataObj($relatedForm[0]),
                { period } = formData,
                requestParameters = {
                    url: path.replace('@', period),
                    type: 'GET',
                };

            $relatedPreloaderWrap.addClass('_loading-data');

            const request = $.ajax(requestParameters);

            request.always(() => {
                setTimeout(() => {
                    $relatedPreloaderWrap.removeClass('_loading-data');
                }, Math.max(0, 1300 - ($.now() - startTime)))
            });

            return request;
        },

        _constructChart(props) {
            const { id, series, labels, fitLabelsFromCount, $currentChart } = props;

            $currentChart.attr('id', id);

            return new Chartist.Line(`#${id}`, {
                labels: labels,
                series: series,
            }, {
                low: 0,
                high: $_._getChartHigh(series),
                chartPadding: 0,
                axisY: {
                    offset: 60,
                    labelInterpolationFnc: (value) => {
                        return _formatBigNum(value)
                    },
                },
                axisX: {
                    offset: 50,
                },
            })
                .on('created', () => {
                    $_._clearTooltips($currentChart, true);
                })
                .on("draw", (data) => {
                    const { series, index, type, axis={} } = data;

                    if (fitLabelsFromCount && type === 'label' && data.axis.units.dir === 'horizontal' && (axis.ticks.length >= fitLabelsFromCount)) {
                        $(data.element._node.firstChild).wrap($(`<span class="rotate-wrap"></span>`));
                    }

                    if (type === 'grid' && axis.units.dir === 'horizontal') {
                        const
                            $currentNode = $(data.element._node),
                            $hoverHandler = $currentNode.clone().addClass('_hover-handler').css('stroke-width', axis.stepLength);

                        $currentNode.before($hoverHandler)

                        $hoverHandler.on("mouseenter", () => {
                            $hoverHandler.addClass('_hover');
                            $currentChart.find(`[data-point="${index}"]`).addClass('_hover').trigger('mouseenter');
                            $_._fitTooltips();
                        })

                        $hoverHandler.on("mouseleave", () => {
                            $hoverHandler.removeClass('_hover');
                            $currentChart.find(`[data-point="${index}"]`).removeClass('_hover').trigger('mouseleave');
                        });
                    }

                    if (type === "point") {
                        const
                            { index, seriesIndex } = data,
                            val = series[index];

                        if (val) {
                            const circle = new Chartist.Svg('circle', {
                                cx: [data.x],
                                cy: [data.y],
                                'ct:value': data.value.y,
                                'ct:meta': data.meta,
                                class: 'custom-point',
                            });

                            data.element.replace(circle);
                            circle._node.setAttribute("title", _formatCurrencyCa(val));
                            circle._node.setAttribute("data-point", index);

                            $(circle._node).tooltip({
                                show: { duration: 0 },
                                hide: { duration: 0 },
                                classes: {
                                    "ui-tooltip-content": $_.colorMods[seriesIndex],
                                },
                                position: {
                                    my: "center+5 bottom-5",
                                    at: "center bottom",
                                    collision: "flip",
                                },
                            });
                        } else {
                            data.element.remove();
                        }
                    }
                });
        },

        _getChartHigh(series) {
            return Math.max(...series.reduce((total, current) => total.concat(current), []))*1.2;
        },

        _fitTooltips() {
            const $tooltips = $('.ui-tooltip').toArray().map(item => {
                    const
                        $item = $(item),
                        top = $item.position().top,
                        height = $item.height();

                    return {
                        top: top,
                        height: height,
                        bottom: top + height,
                        item: $item,
                    }
                }).sort((a,b) => b.top - a.top);

            for (let i = 0; i < $tooltips.length; i++) {
                if ($tooltips[i+1]) {
                    if (($tooltips[i+1].bottom + 5 >= $tooltips[i].top) || ($tooltips[i+1].top === $tooltips[i].top)) {
                        const newTop = $tooltips[i+1].top - ($tooltips[i+1].bottom - $tooltips[i].top + 5)

                        $tooltips[i+1].item.css('top', newTop);
                        $tooltips[i+1].top = newTop;
                        $tooltips[i+1].bottom = newTop + $tooltips[i+1].height;
                    }
                }
            }
        },

        _updateChart(props) {
            const
                { chart, data } = props,
                { series } = data;

            chart.update(data, { high: $_._getChartHigh(series) }, true);
        },

        _bindFormChangeEvent(props) {
            const { $currentChart, $relatedForm, $relatedPreloaderWrap, chart, path } = props;

            $relatedForm.on('change', () => {
                $_._loadData({ path, $relatedForm, $relatedPreloaderWrap }).done((data) => {
                    $_._clearTooltips($currentChart, true);
                    $_._updateChart({ chart, data: $_._parseData(data) });
                });
            });
        },

        _clearTooltips($chart, clearHelpers) {
            $chart.find('[data-point]').tooltip('close');
            if (clearHelpers) $('.ui-helper-hidden-accessible, .ui-tooltip').remove();
        },

        _parseData(data) {
            const { labels, series } = data;

            return {
                labels,
                series: series.map(item => item.map(item => parseInt(item)))
            }
        },
    };

    $(document).ready(() => {
        $_.init();
    });
});
