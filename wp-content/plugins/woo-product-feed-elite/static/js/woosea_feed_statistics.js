jQuery(function ($) {
  var i18n = woosea_feed_statistics_js_object.i18n;

  var history_products_chart_data = $('#history_products_chart_data').val();
  if (history_products_chart_data === '') {
    return;
  }
  history_products_chart_data = JSON.parse(history_products_chart_data);
  history_products_chart_data = Object.entries(history_products_chart_data);

  var plot = $.plot('#placeholder', [history_products_chart_data], {
    series: {
      label: i18n.legend_label,
      color: '#3498db',
      bars: {
        show: true,
        fill: true,
        hoverable: true,
        lineWidth: 0,
        barWidth: 0.8,
        align: 'center',
        fillColor: '#3498db',
      },
    },

    xaxis: {
      mode: 'categories',
      color: '#aaa',
      position: 'bottom',
      tickColor: 'transparant',
      tickLength: 1,
      font: {
        color: '#aaa',
      },
    },

    yaxis: {
      min: 0,
      minTickSize: 1,
      tickDecimals: 0,
      color: '#d4d9dc',
      font: {
        color: '#aaa',
      },
    },

    grid: {
      color: '#aaa',
      borderColor: 'transparent',
      borderWidth: 0,
      hoverable: true,
    },

    hooks: {
      draw: [
        function (plot, ctx) {
          // Draw value labels on top of bars
          var series = plot.getData()[0];
          var plotOffset = plot.getPlotOffset();
          var axes = plot.getAxes();

          ctx.save();
          ctx.fillStyle = '#333';
          ctx.font = '12px Arial';
          ctx.textAlign = 'center';
          ctx.textBaseline = 'bottom';

          for (var i = 0; i < series.data.length; i++) {
            var datapoint = series.data[i];
            var x = axes.xaxis.p2c(i) + plotOffset.left;
            var y = axes.yaxis.p2c(datapoint[1]) + plotOffset.top - 5;

            ctx.fillText(datapoint[1], x, y);
          }

          ctx.restore();
        },
      ],
    },
  });

  // Add tooltip functionality
  $('#placeholder').bind('plothover', function (event, pos, item) {
    if (item) {
      var x = item.datapoint[0],
        y = item.datapoint[1];

      $('#tooltip').remove();

      var tooltip =
        '<div id="tooltip" style="position: absolute; display: none; padding: 5px 10px; background: rgba(0,0,0,0.8); color: white; border-radius: 5px; font-size: 12px; z-index: 1000;">' +
        history_products_chart_data[x][0] +
        ': ' +
        y +
        '</div>';

      $('body').append(tooltip);

      $('#tooltip')
        .css({
          top: item.pageY - 35,
          left: item.pageX - $('#tooltip').outerWidth() / 2,
        })
        .fadeIn(200);
    } else {
      $('#tooltip').remove();
    }
  });

  // Add the Flot version string to the footer
  $('#footer').prepend('Flot ' + jQuery.plot.version + ' &ndash; ');
});
