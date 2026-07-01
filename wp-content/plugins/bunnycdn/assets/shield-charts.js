/**
 * bunny.net WordPress Plugin
 * Copyright (C) 2024-2026 BunnyWay d.o.o.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

function renderShieldChart(elId, data, xLabels) {
    let dataFormatter = (value) => value.toFixed(0);
    let tooltipFormatter = function (data) {
        let result = dateFormatter(data[0].data[0]);
        for (var i = 0; i < data.length; i++) {
            result += '<br>' + data[i].seriesName + ': ' + '<b>' + dataFormatter(data[i].data[1]) + '</b>';
        }

        return result;
    };

    const keys = Object.keys(data);
    const series = [];
    const colors = ['#1870C6', '#D64545', '#FF7854', '#2FC584'];

    for (var i = 0; i < keys.length; i++) {
        series.push({
            name: xLabels[i],
            data: data[keys[i]],
            smooth: true,
            showSymbol: false,
            type: 'line',
            color: colors[i]
        });
    }

    const chart = echarts.init(document.querySelector(`div[data-chart="${elId}"]`));
    chart.setOption({
        xAxis: {
            type: 'time',
            axisLabel: {
                formatter: dateFormatter,
                color: '#687a8b'
            },
            axisLine: {
                lineStyle: {
                    color: '#9BA7B2',
                },
            },
        },
        yAxis: {
            type: 'value',
            axisLabel: {
                formatter: dataFormatter,
                color: '#687a8b'
            },
        },
        tooltip: {
            trigger: 'axis',
            formatter: tooltipFormatter,
            axisPointer: {
                type: 'none'
            },
        },
        series: series,
    });

    window.addEventListener('resize', () => {
        chart.resize();
    });

    window.addEventListener('popstate', (e) => {
        chart.resize();
    });
}

if (document.querySelector('main article.shield div.bn-chart [data-chart="shield-ddos"]')) {
    jQuery.ajax({
        url: ajaxurl,
        data: {
            action: 'bunnycdn',
            section: 'shield',
            perform: 'statistics-ddos',
            // _wpnonce: ommited for GET requests
        },
        type: 'GET',
        complete: function (response) {
            if (response?.responseJSON?.success === true) {
                const data = response.responseJSON.data;
                renderShieldChart('shield-ddos', data.chart, ['Logged', 'Blocked', 'Challenged', 'Verified']);
                return;
            }

            let message = 'Could not load statistics';
            if (response?.responseJSON?.data?.message !== undefined) {
                message = response.responseJSON.data.message;
            }
            console.error(message);
        }
    });
}

if (document.querySelector('main article.shield div.bn-chart [data-chart="shield-waf"]')) {
    jQuery.ajax({
        url: ajaxurl,
        data: {
            action: 'bunnycdn',
            section: 'shield',
            perform: 'statistics-waf',
            // _wpnonce: ommited for GET requests
        },
        type: 'GET',
        complete: function (response) {
            if (response?.responseJSON?.success === true) {
                const data = response.responseJSON.data;
                renderShieldChart('shield-waf', data.chart, ['Triggers']);
                return;
            }

            let message = 'Could not load statistics';
            if (response?.responseJSON?.data?.message !== undefined) {
                message = response.responseJSON.data.message;
            }
            console.error(message);
        }
    });
}
