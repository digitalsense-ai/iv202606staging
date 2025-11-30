/**
 * Page Global Search
 */

'use strict';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Datatable (jquery)
$(function () {
  
  // Variable declaration for table
  //var globalSearchUrl = baseUrl + 'global-search/';
  //var globalSearchRefreshUrl = baseUrl + 'global-search-refresh/';
  let completedBatches = 0;  // To track how many batches are completed
  let totalBatches = ($('#pending_batches').val() == '') ? 0 : $('#pending_batches').val();  // Total number of batches to monitor
  // let intervalIds = [];  // To keep track of interval IDs for each batch

  let totalJobs = 0;
  let completedJobs = 0; 

// //console.log("totalBatches : " + totalBatches);
//   window.Pusher = Pusher;
//   window.Echo = new Echo({
//   //const echo = new Echo({    
//       broadcaster: 'pusher',
//       key: 'bc3c5712d049ef05fcb5',
//       cluster: 'eu',
//       //encrypted: true,
//       //debug: true  // Enable debugging
//       forceTLS: true
//   });

  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  }); 
    
  //Refesh      
  $(document).on('click', '.btn-refresh-global-search', function () { 
    var btn_refresh = $(this);
    btn_refresh.attr('disabled','disabled');
    btn_refresh.addClass('disabled');
    btn_refresh.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span>' +
        'Refreshing...');
    btn_refresh.hide();
    
    // if(totalBatches == 0)
    // {
    //   $('.progress').show();
    //   $('.progress .progress-bar').attr('style', 'width: 5%;');
    //   $('.progress .progress-bar span').html('5%');
    // }
    $('#has_pending').val('');
      $('.progress').show();
      $('.progress .progress-bar').attr('style', 'width: 0%;');
      $('.progress .progress-bar span').html('0%');

    var client_id = $('#client').find('option:selected').val();

    $.ajax({              
      url: `${baseUrl}global-search-refresh`,
      type: 'GET',     
      data: {client_id: client_id},      
      success: function (result) {   
        console.log(result);
        
        // $('.progress').show();
        // $('.progress .progress-bar').attr('style', 'width: 10%;');
        // $('.progress .progress-bar span').html('10%');
       
        totalBatches = result['batchIds'].length;       

        if(totalBatches > 0)
        {
          var batchId = 0;//result['batchIds'][0]['batchId'];
          //checkBatchStatus(batchId);
          let intervalId = setInterval(function() {      
            checkBatchStatus(batchId, intervalId);
          }, 2000); 
        }
        else
        {
          $('.progress .progress-bar').attr('style', 'width: 100%;');
          $('.progress .progress-bar span').html('100%');

          Swal.fire({
            icon: 'warning',
            title: `No new data's!`,
            text: `No new data's found in Global search.`,
            customClass: {
              confirmButton: 'btn btn-success'
            }
          }).then(function() {
            totalJobs = 0;
            completedJobs = 0;
            $('#client').val('');
            $('#has_pending').val('completed');
            
            $('.progress').hide();
            $('.progress .progress-bar').attr('style', 'width: 0%;');
            $('.progress .progress-bar span').html('0%');

            var btn_refresh = $('.btn-refresh-global-search');
            btn_refresh.removeAttr('disabled');
            btn_refresh.removeClass('disabled');
            btn_refresh.html('Refresh'); 
            btn_refresh.show();              
          });
        }

        /*
        if(result['message'] == 'Done')
        {                    
          totalBatches = result['batchIds'].length;  // Total number of batches to monitor
          
          $.each(result['batchIds'], function (index, batch) {
            var batchId = batch['batchId'];
            var start_period = moment(batch['start_period']).format('MMM-Y');
            var end_period = moment(batch['end_period']).format('MMM-Y');

            console.log(batchId);

            if(batchId == 0)
            {
              var status_text = $('#status-text').html();
              $('#status-text').html(status_text + '<br>No data\'s for this period ' + start_period + ' - ' + end_period);
            }
            else
            {
              // Start the polling for each batch
              let intervalId = setInterval(function() {
                  checkBatchStatus(batchId, intervalId);
              }, 2000);
            console.log(intervalId);  
              // Store the interval ID for stopping later
              intervalIds.push(intervalId);

              console.log(intervalIds); 
            } 
          });
        }
        */
      },
      error: function (err) {
        console.log(err);
      }
    });
  });  

  /*
  function checkBatchStatus(batchId, intervalId) {
    console.log(batchId);  
    $.ajax({
        //url: '/batch-status/' + batchId, // Your API endpoint
        url: `${baseUrl}global-search-refresh/batch-status/` + batchId,  
        method: 'GET',        
        success: function(response) {
          console.log(response);  
          // Assume the response has a "progress" field that gives the batch progress percentage
          if (response.status === 'processing') {
              // Update the progress bar based on the "progress" field from the response
              let progress = response.progress || 0;  // Default to 0 if no progress is returned
              updateProgressBar(progress);
          }
          
          // If the batch is finished (either completed or failed), stop the polling
          if (response.status === 'completed' || response.failed) {
              completedBatches++;  // Increment completed batches count
              clearInterval(intervalId); // Stop polling for this batch
console.log(completedBatches + ' ===  ' + totalBatches);
              // If all batches are finished, call the allBatchesFinished function
              if (completedBatches === totalBatches) {
                  allBatchesFinished();
              }
          }

            // // Check if the batch is finished
            // if (response.status === 'completed' || response.failed) {
            //     completedBatches++;

            //     // If the batch is finished (either completed or failed), stop its polling
            //     clearInterval(intervalId);

            //     // If all batches are finished, stop all intervals and perform your action
            //     if (completedBatches === totalBatches) {
            //       Swal.fire({
            //         icon: 'success',
            //         title: `Successfully refreshed!`,
            //         text: `Global search datas refreshed successfully.`,
            //         customClass: {
            //           confirmButton: 'btn btn-success'
            //         }
            //       }).then(function() {
            //         $('#client').val('');
                    
            //         btn_refresh.removeAttr('disabled');
            //         btn_refresh.removeClass('disabled');
            //         btn_refresh.html('Refresh');               
            //       });
            //     }
            // }
        },
        error: function(xhr, status, error) {
            // Log the error message
            console.log('Error checking status for batch ' + batchId + ': ' + error);
            
            // Stop the interval if there's an error
            clearInterval(intervalId);

            // Optionally, you can show an error message to the user
            var status_text = $('#status-text').html();
            $('#status-text').html(status_text + '<br>Error checking batch status. Please try again.');
        }
    });
  } 

  function updateProgressBar(progress) {
      // Update the width of the progress bar and show the progress percentage
      $('#progressBar').css('width', progress + '%');
      $('#progressBar').attr('aria-valuenow', progress);
      $('#progressBar').find('.sr-only').text(progress + '% Complete');
  }

  function allBatchesFinished() {
    console.log('All batches have finished processing.');

    var status_text = $('#status-text').html();
    $('#status-text').text(status_text + '<br>All batches are completed!');

     Swal.fire({
      icon: 'success',
      title: `Successfully refreshed!`,
      text: `Global search datas refreshed successfully.`,
      customClass: {
        confirmButton: 'btn btn-success'
      }
    }).then(function() {
      $('#client').val('');
      
      btn_refresh.removeAttr('disabled');
      btn_refresh.removeClass('disabled');
      btn_refresh.html('Refresh');               
    });
  }
  */
     
  function checkBatchStatus(batchId, intervalId) {
    var client_id = $('#client').val();

      $.ajax({        
        url: `${baseUrl}global-search-refresh/batch-status/` + batchId,  
        data: {client_id: ((client_id) ? client_id : null) },
        method: 'GET',        
        success: function(response) {
          console.log(response);   
          var btn_refresh = $('.btn-refresh-global-search');

          if(response['status'] == 'processing')
          {  
            if(response['pending_jobs'] == 0)
            {
              $('#client').val('');
              $('#has_pending').val('completed');

              $('.progress').hide();
              $('.progress .progress-bar').attr('style', 'width: 0%;');
              $('.progress .progress-bar span').html('0%');
              
              btn_refresh.removeAttr('disabled');
              btn_refresh.removeClass('disabled');
              btn_refresh.html('Refresh'); 
              btn_refresh.show(); 
//console.log("processing " + intervalId);
              if(intervalId)
                clearInterval(intervalId);
            }
            else
            {
              btn_refresh.hide(); 
console.log("totalJobs: " + totalJobs);
console.log("completedJobs: " + completedJobs);
              // Generate random number between 1 and 75
              //var progress = Math.floor(Math.random() * 75) + 1;
              if(totalJobs == 0)
                totalJobs = response['pending_jobs'];
//console.log("totalJobs " + totalJobs);
              //let totalJobs = response['pending_jobs'];
              let singleJob = 100/totalJobs;

              // if(completedJobs > totalJobs)
              //   completedJobs = 0;
              // else
              //   completedJobs++;

              if(response['pending_jobs'] > 0)
              {
                completedJobs = totalJobs - response['pending_jobs'];
                console.log("ssss " + completedJobs);
              }

              var progress = Math.floor(completedJobs * singleJob);

              if(progress > 100)
                progress = 100;

//console.log(completedJobs + ' ' + progress);
              $('.progress').show();   
              $('.progress .progress-bar').attr('style', 'width: ' + progress + '%;');
              $('.progress .progress-bar span').html(progress + '%');
            }
          }
          else if(response['status'] == 'unknown')  
          {
            //console.log($('#has_pending').val());
            //$('.progress').hide();
            if($('#has_pending').val() == '')
            {         //console.log(completedJobs + ' ~~~~~~~~~~~~~ ' + totalJobs);
              if(totalJobs == 0)
              {
                $('#client').val('');
                //$('#has_pending').val('completed');

                $('.progress').hide();
                $('.progress .progress-bar').attr('style', 'width: 0%;');
                $('.progress .progress-bar span').html('0%');
                
                btn_refresh.removeAttr('disabled');
                btn_refresh.removeClass('disabled');
                btn_refresh.html('Refresh'); 
                btn_refresh.show(); 
              }
              else
              {
                if(completedJobs === (totalJobs - 1))
                {                             
                  $('.progress .progress-bar').attr('style', 'width: 100%;');
                  $('.progress .progress-bar span').html('100%');

                  Swal.fire({
                    icon: 'success',
                    title: `Successfully refreshed!`,
                    text: `Global search datas refreshed successfully.`,
                    customClass: {
                      confirmButton: 'btn btn-success'
                    }
                  }).then(function() {
                    totalJobs = 0;
                    completedJobs = 0;
                    $('#client').val('');
                    $('#has_pending').val('completed');
                    
                    $('.progress').hide();
                    $('.progress .progress-bar').attr('style', 'width: 0%;');
                    $('.progress .progress-bar span').html('0%');

                    //var btn_refresh = $('.btn-refresh-global-search');
                    btn_refresh.removeAttr('disabled');
                    btn_refresh.removeClass('disabled');
                    btn_refresh.html('Refresh'); 
                    btn_refresh.show();              
                  });
                }  
              }
            }
            else if($('#has_pending').val() == 'completed')
            {console.log("completeddddddddddd");
              $('#has_pending').val('');
            }
//console.log("unknown " + intervalId);
             
            if(intervalId)  
              clearInterval(intervalId);
          }       
        },
        error: function(xhr, status, error) {
            // Log the error message
            console.log('Error checking status for batch ' + batchId + ': ' + error);   

            clearInterval(intervalId);                 
        }
      });
  }
     
     /*
  //var url = window.location.href;
//console.log(url);
//console.log(url.substring(url.lastIndexOf('/') + 1));
  //if(url.substring(url.lastIndexOf('/') + 1) != "global-search")  
  //{  
    //window.Echo.channel('com-sales-invoices-progress-channel.{{ $batchId }}').listen('.ImportReconciliationComSalesInvoicesJobProgressEvent', (event) => {
    window.Echo.channel('com-sales-invoices-progress-channel').listen('.ImportReconciliationComSalesInvoicesJobProgressEvent', (event) => {

      //console.log(event);
      let progress = event.progress;
      //console.log(progress);      

      if(progress == 100)
        completedBatches++;
      //document.getElementById('progress-bar').style.width = progress + '%';   
      $('.progress').show();   
      $('.progress .progress-bar').attr('style', 'width: ' + progress + '%;');
      $('.progress .progress-bar span').html(progress + '%');


      console.log(totalBatches + ' -- ' + completedBatches);
      if(totalBatches == 0)
      {
        var batchId = event.batchId;
        if($('#has_pending').val() == '')  
          checkBatchStatus(batchId);
      }
      else
      {
        if(completedBatches === totalBatches)
        {
           Swal.fire({
            icon: 'success',
            title: `Successfully refreshed!`,
            text: `Global search datas refreshed successfully.`,
            customClass: {
              confirmButton: 'btn btn-success'
            }
          }).then(function() {
            $('#client').val('');
            
            $('.progress').hide();
            $('.progress .progress-bar').attr('style', 'width: 0%;');
            $('.progress .progress-bar span').html('0%');

            var btn_refresh = $('.btn-refresh-global-search');
            btn_refresh.removeAttr('disabled');
            btn_refresh.removeClass('disabled');
            btn_refresh.html('Refresh'); 
            btn_refresh.show();              
          });
        }
      }
    });    
  //}
*/
  if(totalBatches == 0)
  {    
    if($('#has_pending').val() == '')
    {
      var batchId = 0;
      let intervalId = setInterval(function() {      
        checkBatchStatus(batchId, intervalId);
      }, 2000); 
    }   
  }
});