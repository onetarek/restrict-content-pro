"use strict";

let $ = jQuery;
let form,
	submit_button,
	progress_bar,
	progress_text,
	items_processed,
	items_processed_number,
	message;

let RCP_Batch = {

	/**
	 * Listens for job submissions and initiates job processing.
	 */
	listen: function () {

		let batch = this;

		form = $('#rcp-batch-processing-form');

		submit_button = form.find('.button-primary')

		progress_bar = $('#rcp-batch-processing-job-progress-bar');

		progress_text = $('#rcp-batch-processing-job-progress-text');

		items_processed = $('#rcp-batch-processing-job-items-processed');

		items_processed_number = $('#rcp-batch-processing-job-items-processed span');

		message = $('#rcp-batch-processing-message');

		form.on('submit', function (event) {

			event.preventDefault();

			submit_button.prop('disabled', true).hide();

			message.html('');

			let data = {
				action: 'rcp_process_batch',
				job_id: $('#rcp-job-id').val(),
				rcp_batch_nonce: rcp_batch_vars.batch_nonce
			};

			batch.process(data, 1);

		});
	},

	/**
	 * Process the specified job.
	 *
	 * @param object data Job data
	 * @param int    step Step to process
	 */
	process: function(data, step) {
		let batch = this;

		data.step = step;

		$.ajax({
			dataType: 'json',
			data: data,
			type: 'POST',
			url: ajaxurl,
			success: function(response) {
				// TODO: clean up this whole function
				if(false === response.success) {

					console.log('batch not successful');

					console.log(response.data.message);

					submit_button.prop('disabled', false).show();

					message.html('<p>' + response.data.message + ' ' + rcp_batch_vars.i18n.job_retry + '</p>');

					return;
				}

				if(response.data.complete) {

					progress_bar.progressbar({value:response.data.percent_complete});

					progress_text.text(response.data.percent_complete + '%');

					items_processed.show();

					items_processed_number.text(response.data.items_processed);

					message.html('<p>' + rcp_batch_vars.i18n.job_success + '</p>');

				} else if(response.data.error) {
					// TODO: show errors
					console.log('error processing batch');

					console.log(response.data.error);

				} else if(response.data.next_step) {

					progress_bar.progressbar({value:response.data.percent_complete});

					progress_text.text(response.data.percent_complete + '%');

					items_processed.show();

					items_processed_number.text(response.data.items_processed);

					batch.process(data, response.data.next_step);

				} else {

					// wtf happened
					console.log(response);

					$('#rcp-batch-processing-message').html('<p>' + response.data.message + ' ' + rcp_batch_vars.i18n.job_retry + '</p>');

				}
			},
			error: function(response) {
				console.log(new Date() + ' error');
				console.log(response);
			}
		});
	}
}

/**
 * Loads the job listener.
 */
$(document).ready(function() {
	RCP_Batch.listen();
} );