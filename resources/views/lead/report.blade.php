@extends('layouts.main')
@section('title',  __('form.lead') . ' : ' . __('form.report') )
@section('content')
<div class="main-content" style="margin-bottom: 20px;">
   <div class="row">
   	<div class="col-md-12">
   		<h5>{{ __('form.lead') . ' : ' . __('form.report') }}</h5>
   		<hr>
   	</div>
      <div class="col-md-6">
         <canvas id="chart-area"></canvas>
      </div>
      <div class="col-md-6">
         <canvas id="sources_conversion"></canvas>
      </div>
   </div>
</div>
<div class="main-content">
   <form>
      <div class="form-row">
         <div class="form-group col-md-2">
            <?php
               echo form_dropdown('month', $data['months'] , strtolower(date("F")) , "class='form-control selectPickerWithoutSearch ' ");
               ?>
         </div>
      </div>
   </form>
   <canvas id="monthly_conversion"></canvas>
</div>
@endsection
@section('onPageJs')
<script>

window.chartColors = {
	red: 'rgb(255, 99, 132)',
	orange: 'rgb(255, 159, 64)',
	yellow: 'rgb(255, 205, 86)',
	green: 'rgb(75, 192, 192)',
	blue: 'rgb(54, 162, 235)',
	purple: 'rgb(153, 102, 255)',
	grey: 'rgb(201, 203, 207)'
};



var color = Chart.helpers.color;

// Conversion by Sources Chart
		var ctx = document.getElementById("sources_conversion");
		var myChart = new Chart(ctx, {
		  type: 'bar',
		  data: {
		    labels: <?php echo json_encode($data['sources_conversion']['labels']); ?>,
		    datasets: [{
		      label: '# of Tomatoes',
		      data: <?php echo json_encode($data['sources_conversion']['data']); ?>,
		      backgroundColor: color(window.chartColors.purple).alpha(0.5).rgbString()
		    }]
		  },
		  options: {
		  	legend: {
		        display: false
		    },
		    tooltips: {
		        callbacks: {
		           label: function(tooltipItem) {
		                  return tooltipItem.yLabel;
		           }
		        }
		    },

		    responsive: true,
			title: {
				display: true,
				text: '<?php echo __("form.sources_conversion") ?>'
			},
		    scales: {
		      xAxes: [{
		        ticks: {
		          maxRotation: 0,
		          minRotation: 0
		        }
		      }],
		      yAxes: [{
		        ticks: {
		          beginAtZero: true
		        }
		      }]
		    }
		  }
		});
// End of Conversion by Sources Chart

// Conversion by Week Chart
		var config = {
				type: 'pie',
				data: {
					datasets: [{
						data: <?php echo json_encode($data['conversion_this_week']['data']); ?>,
						backgroundColor: [
							window.chartColors.red,
							window.chartColors.orange,
							window.chartColors.yellow,
							window.chartColors.green,
							window.chartColors.blue,
							window.chartColors.purple,
							window.chartColors.grey
						],
						label: 'Dataset 1'
					}],
					labels: <?php echo json_encode($data['conversion_this_week']['labels']); ?>
				},
				options: {
					responsive: true,
					title: {
						display: true,
						text: '<?php echo __("form.this_weeks_leads_conversion") ?>'
					}
				}
			};

		window.onload = function() {
			var ctx = document.getElementById('chart-area').getContext('2d');
			window.myPie = new Chart(ctx, config);
		};

// End of Conversion by Week Chart

// Conversion by Month Chart

		var conversion_by_month_chart_data = {
			    labels: <?php echo json_encode($data['conversion_by_month']['labels']); ?>,
			    datasets: [{
			      label: '# of Tomatoes',
			      data: <?php echo json_encode($data['conversion_by_month']['data']); ?>,
			      backgroundColor: color(window.chartColors.green).alpha(0.5).rgbString()
			    }]
			  };
		var ctx = document.getElementById("monthly_conversion");
		window.conversion_by_month_chart = new Chart(ctx, {
			  type: 'bar',
			  data: conversion_by_month_chart_data,
			  options: {
			  	legend: {
			        display: false
			    },
			    tooltips: {
			        callbacks: {
			           label: function(tooltipItem) {
			                  return tooltipItem.yLabel;
			           }
			        }
			    },

			    responsive: true,
				title: {
					display: true,
					text: '<?php echo __("form.leads_conversion_by_month") ?>'
				},
			    scales: {
			      xAxes: [{
			        ticks: {
			          maxRotation: 90,
			          minRotation: 80
			        }
			      }],
			      yAxes: [{
			        ticks: {
			          beginAtZero: true
			        }
			      }]
			    }
			  }
		});



		$('select[name=month]').change(function(){			

			postData = { "_token" : "{{ csrf_token() }}" , month : $(this).val()  };

	        $.post( "{{ route('get_report_conversion_by_month_for_graph') }}" , postData ).done(function( response ) {
	            
	                conversion_by_month_chart_data.datasets.forEach(function(dataset) {
						dataset.data = response.conversion_by_month.data;
					});			
					conversion_by_month_chart_data.labels = response.conversion_by_month.labels;
					window.conversion_by_month_chart.update();


	        }, 'json');


			


		});


// End of Conversion by Month Chart
</script>
@endsection