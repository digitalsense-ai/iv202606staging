/**
 * Common
 */

'use strict';

$(function () {      
  // const periodmap = {
  //             "no_1": "january-february",
  //             "no_2": "march-april",
  //             "no_3": "may-june",
  //             "no_4": "july-august",
  //             "no_5": "september-october",
  //             "no_6": "november-december",
  //             "uk_1": "january-february-march",
  //             "uk_2": "february-march-april",
  //             "uk_3": "march-april-may",
  //             "uk_4": "april-may-june",
  //             "uk_5": "may-june-july",
  //             "uk_6": "june-july-august",
  //             "uk_7": "july-august-september",
  //             "uk_8": "august-september-october",
  //             "uk_9": "september-october-november",
  //             "uk_10": "october-november-december",
  //             "uk_11": "november-december-january",
  //             "uk_12": "december-january-february"
  //         };

  const periodmap = {

    /* ===== Monthly (12) ===== */
    ...Object.fromEntries(
      ['at','cz','fi','fr','lu','pl','pt']
        .flatMap(c => [
          [ `${c}_1`,  'january' ],
          [ `${c}_2`,  'february' ],
          [ `${c}_3`,  'march' ],
          [ `${c}_4`,  'april' ],
          [ `${c}_5`,  'may' ],
          [ `${c}_6`,  'june' ],
          [ `${c}_7`,  'july' ],
          [ `${c}_8`,  'august' ],
          [ `${c}_9`,  'september' ],
          [ `${c}_10`, 'october' ],
          [ `${c}_11`, 'november' ],
          [ `${c}_12`, 'december' ],
        ])
    ),

    /* ===== Quarterly (4) ===== */
    ...Object.fromEntries(
      ['be','it','nl','es','se','ch','us']
        .flatMap(c => [
          [ `${c}_1`, 'january-march' ],
          [ `${c}_2`, 'april-june' ],
          [ `${c}_3`, 'july-september' ],
          [ `${c}_4`, 'october-december' ],
        ])
    ),

    /* ===== Denmark (quarter + half year) ===== */
    ...Object.fromEntries(
      ['dk'].flatMap(c => [
        [ `${c}_1`, 'january-march' ],
        [ `${c}_2`, 'april-june' ],
        [ `${c}_3`, 'july-september' ],
        [ `${c}_4`, 'october-december' ],
        [ `${c}_5`, 'january-june' ],
        [ `${c}_6`, 'july-december' ],
      ])
    ),

    /* ===== Bi-monthly (IE, NO) ===== */
    ...Object.fromEntries(
      ['ie','no'].flatMap(c => [
        [ `${c}_1`, 'january-february' ],
        [ `${c}_2`, 'march-april' ],
        [ `${c}_3`, 'may-june' ],
        [ `${c}_4`, 'july-august' ],
        [ `${c}_5`, 'september-october' ],
        [ `${c}_6`, 'november-december' ],
      ])
    ),

    /* ===== Germany (complex but still grouped) ===== */
    ...Object.fromEntries(
      [
        ['de_1','january-march'],
        ['de_2','april-june'],
        ['de_3','july-september'],
        ['de_4','october-december'],
        ['de_5','january-june'],
        ['de_6','july-december'],
        ['de_7','january-december'],
        ['de_8','january'],
        ['de_9','february'],
        ['de_10','march'],
        ['de_11','april'],
        ['de_12','may'],
        ['de_13','june'],
        ['de_14','july'],
        ['de_15','august'],
        ['de_16','september'],
        ['de_17','october'],
        ['de_18','november'],
        ['de_19','december'],
      ]
    ),

    /* ===== UK rolling periods ===== */
    ...Object.fromEntries(
      [
        ['uk_1','january-february-march'],
        ['uk_2','february-march-april'],
        ['uk_3','march-april-may'],
        ['uk_4','april-may-june'],
        ['uk_5','may-june-july'],
        ['uk_6','june-july-august'],
        ['uk_7','july-august-september'],
        ['uk_8','august-september-october'],
        ['uk_9','september-october-november'],
        ['uk_10','october-november-december'],
        ['uk_11','november-december-january'],
        ['uk_12','december-january-february'],
      ]
    ),
  };


  $(document).on("show.bs.modal", ".modal-file", function(event) { 
    // Init custom option check
    window.Helpers.initCustomOptionCheck();
  });


  $(document).on("show.bs.modal", ".modal-onboarding", function(event) {    
    // Init custom option check
    window.Helpers.initCustomOptionCheck();

    var modal_id = '#' + $(this).attr("id");

    carouselNormalization(modal_id);
    
    checkitem(modal_id);
    
    var onboardingModalCarousel = document.querySelector(modal_id + ' .carousel');      
      onboardingModalCarousel.addEventListener('slid.bs.carousel', function (event) {        
        checkitem(modal_id);
    });
  });
  
  window.checkitem = function checkitem(modal_id) {
      var $this = $(modal_id + ' .carousel');
      
      if ($(modal_id + ' .formEmail .carousel-inner').length > 0) {        
        if ($(modal_id + ' .formEmail .carousel-inner .carousel-item:first').hasClass('active')) {
            // Hide left arrow
            $this.children('.carousel-control-prev.carousel-control').hide();
            // But show right arrow
            $this.children('.carousel-control-next.carousel-control').show();
        } else if ($(modal_id + ' .formEmail .carousel-inner .carousel-item:last').hasClass('active')) {
            // Hide right arrow
            $this.children('.carousel-control-next.carousel-control').hide();
            // But show left arrow
            $this.children('.carousel-control-prev.carousel-control').show();
        } else {
            $this.children('.carousel-control').show();
        }
      }
      else
      {
        if(modal_id.indexOf('onboardingSlideCommentModal-') != -1)
        {
          //console.log(modal_id);
          if ($(modal_id + ' .carousel-inner .formEmail').prev('.carousel-item').hasClass('active')) {
            console.log($this.find('.comment-status').val());
              if($this.find('.comment-status').val() == 1)
              {
                // Hide left and right arrow
                $this.children('.carousel-control-prev.carousel-control').hide();              
                $this.children('.carousel-control-next.carousel-control').show();
                $this.children('.carousel-indicators').show();
              }
              else
              {
                // Hide left and right arrow
                $this.children('.carousel-control-prev.carousel-control').hide();              
                $this.children('.carousel-control-next.carousel-control').hide();
                $this.children('.carousel-indicators').hide();
              }
          } else if ($(modal_id + ' .carousel-inner .formEmail .carousel-item:last').hasClass('active')) {
              // Hide right arrow
              $this.children('.carousel-control-next.carousel-control').hide();
              // But show left arrow
              $this.children('.carousel-control-prev.carousel-control').show();
          } else {
              $this.children('.carousel-control').show();
          }
        }
        else
        {
          if ($(modal_id + ' .carousel-inner .formEmail').prev('.carousel-item').hasClass('active')) {
              if($(modal_id + ' .carousel-inner .formEmail').hasClass('deactivate'))
              {
                // Hide left arrow
                $this.children('.carousel-control-prev.carousel-control').hide();
                // Hide right arrow
                $this.children('.carousel-control-next.carousel-control').hide();
              }
              else
              {
                // Hide left arrow
                $this.children('.carousel-control-prev.carousel-control').hide();
                // But show right arrow
                $this.children('.carousel-control-next.carousel-control').show();
              }
          } else if ($(modal_id + ' .carousel-inner .formEmail .carousel-item:last').hasClass('active')) {
              // Hide right arrow
              $this.children('.carousel-control-next.carousel-control').hide();
              // But show left arrow
              $this.children('.carousel-control-prev.carousel-control').show();
          } else {
              $this.children('.carousel-control').show();
          }
        }
      }
  }
  
  window.isDecimal = function isDecimal(evt, element) {
      var charCode = (evt.which) ? evt.which : evt.keyCode
      if (          
          (charCode != 46 || $(element).val().indexOf('.') != -1) &&      // “.” CHECK DOT, AND ONLY ONE.
          (charCode < 48 || charCode > 57))
          return false;

      return true;
  }

  window.capitalizeFirstLetter = function capitalizeFirstLetter(string) {
    if(string != '')
      return string.charAt(0).toUpperCase() + string.slice(1);
  }

    //Close C79, Import VAT File, Documents, PIVS Modal
    $(document).on("hide.bs.modal", ".modal-file", function(event) {console.log("modal close");
      var modal_id = '#' + $(this).attr("id");    
      var data = $(modal_id).data();
      //console.log(data);
      var file_type_title = data['file_type_title'];  
      var file_type = data['file_type']; 
      var vat_reg_id = data['vat_reg_id'];  
      var client_id = data['client_id'];  
      var file_id = (data['d_id']) ? data['d_id'] : ((data['file_id']) ? data['file_id'] : 0);  

      var id = file_type + '-' + vat_reg_id + '-' + file_id;
     console.log(data['upload_success']);
      if(data['upload_success'] == 1)  
      {  
        if(modal_id.indexOf('uploadModal-') != -1 || modal_id.indexOf('uploadSingleModal-') != -1 || modal_id.indexOf('overwriteModal-') != -1)
        { 
          if(modal_id.indexOf('uploadSingleModal-') != -1)
            $("#accord-"+ id).closest('.accordion-item').hide();
          
          console.log("reload docs");

          if(file_type == 'iranyexcel')
            loadImportReconciliationFileDocs(file_type, file_type_title, client_id, vat_reg_id);
          else  
            loadVATReturnsFileDocs(file_type, file_type_title, client_id, vat_reg_id);
        }
      }
    });

    //Close excel-column-template Modal
    $(document).on("hide.bs.modal", ".excel-column-template-modal", function(event) {
      var data = $(this).data();
      var vat_reg_id = data['vat_reg_id'];
      var client_id = data['client_id'];  
      var file_type = data['file_type'];  

      if(vat_reg_id)
      {
        $(".form-select.excel-column-template").val("0");
        $("#btn-upload-"+ file_type +"-" + vat_reg_id).removeAttr('disabled');

        console.log("reload docs fro excel template");

        if(file_type == 'iranyexcel')
          loadImportReconciliationFileDocs(file_type, 'Any Excel', client_id, vat_reg_id);
        else
          loadVATReturnsFileDocs('vatreturn', 'Excel/XML', client_id, vat_reg_id);
      }
      // else
      // {
      //   console.log("reload docs from excel template");
      // }
    });

    //Coursel item - Height
    // Set all carousel items to the same height
    function carouselNormalization(modal_id) {

      window.heights = [], //create empty array to store height values
        window.tallest; //create variable to make note of the tallest slide

      function normalizeHeights() {        
        $(modal_id + ' .carousel .carousel-inner .carousel-item').each(function() { //add heights to array          
          window.heights.push($(this).outerHeight());
        });
        window.tallest = Math.max.apply(null, window.heights); //cache largest value        
        $(modal_id + ' .carousel .carousel-inner .carousel-item').each(function() {
          $(this).css('min-height',tallest + 'px');
        });
      }
      normalizeHeights();

      $(window).on('resize orientationchange', function () {
        window.tallest = 0, window.heights.length = 0; //reset vars
        $(modal_id + ' .carousel .carousel-inner .carousel-item').each(function() {
          $(this).css('min-height','0'); //reset min-height
        }); 

        normalizeHeights(); //run it again 

      });

      if ($(this).css("display") == 'block' || $(this).css("visibility") == 'visible') {
        was_show = 1;
      } else {
        $(this).addClass("active");
        var was_show = 0;
      }

      window.heights.push($(this).outerHeight());

      if (was_show == 0) {
        $(this).removeClass("active");
      }

    }

    // Search Ul li list in modal
    $(document).on('keyup change', '.search-modal-name-list', function () {
      var data = $(this).data();  

      var search_id = data["search_id"];  
      var search_title = data["search_title"];  
      var searchtext = $(this).val().toLowerCase();

      $('#'+ search_id +'-list li').attr('style','');
      if($.trim(searchtext) != "")
      {
        $('#'+ search_id +'-list li').attr('style','display:none !important');
       
        $('#'+ search_id +'-list li[data-search_name*="' + searchtext + '"]').show();
        $('#'+ search_id +'-list li[data-search_email*="' + searchtext + '"]').show();
      }

      var clientCountTotal = $('#'+ search_id +'-list li').length;
      var clientCount = $('#'+ search_id +'-list li:visible').length;
      if($.trim(searchtext) == "")
        $('#'+search_id+'-count').text(clientCountTotal + " " + search_title);
      else
        $('#'+search_id+'-count').text(clientCount + " of " + clientCountTotal + " " + search_title);

    });

    window.selectedLength = function selectedLength(elementName)
    {     
      var numberChecked = $('input'+elementName+':checked').length;
      $(".selected-no").text("Selected " + numberChecked);
    }

    window.drawDtTable = function drawDtTable(result, type)
    {      
      if(type == 'companies')
      {    
        var companies = result['companies'];

        company_datas = [];
        var vat_countries = "";
        var vat_countries_status = [];
        var company_start = 1;
        $.each(companies, function (idx, company) {
          vat_countries = "";
          vat_countries_status = []; 
          $.each(company['vatregmain'], function (idx, vatregmain) {
            if(vat_countries == '')            
              vat_countries = vatregmain['country'];              
            else           
              vat_countries += ' ' + vatregmain['country'];   

            vat_countries_status.push(vatregmain['status']);
          });
          company_datas.push({         
            'id' : company['id'],
            'fake_id' : company_start,
            'vat_country' : (vat_countries == '') ? '-' : vat_countries,
            'vat_country_status' : vat_countries_status,
            'client_name' : company['client_name'],
            'trading_name' : company['trading_name'],
            'status' : parseInt(company['status'])           
          });
          company_start = company_start + 1;  
        }); 

        return company_datas;
      }//companies 
      else if(type == 'other_companies')
      {    
        var companies = result['other_companies'];

        other_company_datas = [];
        var vat_countries = "";
        var vat_countries_status = []; 
        var company_start = 1;
        $.each(companies, function (idx, company) {
          vat_countries = "";
          vat_countries_status = [];
          $.each(company['vatregmain'], function (idx, vatregmain) {
            if(vat_countries == '')            
              vat_countries = vatregmain['country'];              
            else           
              vat_countries += ' ' + vatregmain['country'];
              
            vat_countries_status.push(vatregmain['status']);
          });
          other_company_datas.push({         
            'id' : company['id'],
            'fake_id' : company_start,
            'vat_country' : (vat_countries == '') ? '-' : vat_countries,
            'vat_country_status' : vat_countries_status,
            'client_name' : company['client_name'],
            'trading_name' : company['trading_name'],
            'status' : parseInt(company['status'])           
          });
          company_start = company_start + 1;  
        }); 

        return other_company_datas;
      }//companies  
      else if(type == 'contacts')
      { 
        var client = result['client'];
        var userclient = client['userclient'];

        contact_datas = [];
        var contact_start = 1;
        $.each(userclient, function (idx, contact) {
          var user = contact['user'];      
          var dvuser = user['dvuser'];

          if(!dvuser['is_deleted'])
          {
            contact_datas.push({         
              'id' : dvuser['user_id'],//contact['id'],
              'fake_id' : contact_start,
              'client_id' : contact['client_id'],
              'name' : dvuser['firstname'] + ' ' + dvuser['lastname'],       
              'firstname' : dvuser['firstname'],           
              'lastname' : dvuser['lastname'],
              'email' : user['email'],
              'telephone' : (dvuser['telephone'] == null) ? "-" : dvuser['telephone'],  
              'designation' : (dvuser['designation'] == null) ? '' : dvuser['designation'],          
              'status' : parseInt(dvuser['status'])
            });
            contact_start = contact_start + 1;
          }                    
        }); 

        return contact_datas;
      }//contacts    
      else if(type == 'vatregmain')
      {
        var client = result['client'];
        var vatregmain = client['vatregmain'];
        var team_users = result['team_users'];

        vat_reg_main_datas = [];
        var vat_reg_main_start = 1;
        var cas_dda_title_no = 0;
        $.each(vatregmain, function (idx, vat_reg_main) {  

          if(vat_reg_main['country'] == 'DK')  
            excise_duty_onoff = 1;
                    
          if(vat_reg_main['country'] == 'GB' || vat_reg_main['country'] == 'NO')  
          {
            cas_dda_onoff = 1;   
            if(cas_dda_title_no == 1 || cas_dda_title_no == 2)            
              cas_dda_title_no = 3;             
            else
            {
              if(vat_reg_main['country'] == 'GB')             
                cas_dda_title_no = 1;              
              else if(vat_reg_main['country'] == 'NO')             
                cas_dda_title_no = 2;              
            }
          }

          var teamusers = [];
          $.each(team_users, function (idx1, team_user) {            
            if(vat_reg_main['id'] == team_user['id'])
            {
              $.each(team_users['vatreg'], function (idx2, vatreg) {
                $.each(vatreg['uservatreg'], function (idx3, uservatreg) {                                  
                  var teamusername = uservatreg['user']['dvuser']['firstname'] + " " + uservatreg['user']['dvuser']['lastname'];
                  if(teamusers.indexOf(teamusername) === -1)
                    teamusers.push(teamusername);
                }); 
              }); 
            }
          });

          vat_reg_main_datas.push({                             
                'id' : vat_reg_main['id'],
                'fake_id' : vat_reg_main_start,
                'client_id' : vat_reg_main['client_id'],       
                'client_name' : vat_reg_main['client_name'],           
                'country' : vat_reg_main['country'],
                'service_start' : moment(vat_reg_main['service_start']).format('MMM-Y'),
                'turnover_date' : moment(vat_reg_main['turnover_date']).format('m-d-Y'),
                'general_periods' : vat_reg_main['general_periods'],
                //'vat_reg_type' : vat_reg_main['vat_reg_type'],
                'product_type' : vat_reg_main['product_type'],
                'status' : parseInt(vat_reg_main['status']),
                'oss' : parseInt(vat_reg_main['oss']),
                'excise_duty' : parseInt(vat_reg_main['excise_duty']),
                'cash_acc_stmt' : parseInt(vat_reg_main['cash_acc_stmt']),
                'duty_defer_acc' : parseInt(vat_reg_main['duty_defer_acc']),
                'team_users' : teamusers 
              });
          vat_reg_main_start = vat_reg_main_start + 1;                    
        });

        if(cas_dda_title_no == 1)        
          cas_dda_title = "Cash Account Statement";        
        else if(cas_dda_title_no == 2)        
          cas_dda_title = "Duty Deferment Account";         
        else if(cas_dda_title_no == 3)        
          cas_dda_title = "Cash Account Statement / Duty Deferment Account"; 
        
        return vat_reg_main_datas;
      }//vatregmain 
      else if(type == 'user')
      {
        var users = result['users'];
          
        user_datas = [];
        var user_start = 1;
        $.each(users, function (idx, user) { 
          var dvuser = user['dvuser'];
          //var role = user['roles'][0];  

          var arr_company = [];
          var company = '';
          var userclients = user['userclient']; 
          $.each(userclients, function (userclient_idx, userclient) {
            if(company == '')
            {
              arr_company.push(userclient['client']['client_name'].toLowerCase());
              company = '<ul class="m-0 p-0"><li>' + userclient['client']['client_name'] + '</li>';
            }
            else
            {
              if(!arr_company.includes(userclient['client']['client_name'].toLowerCase())) 
              {
                arr_company.push(userclient['client']['client_name'].toLowerCase());
                company += '<li>' + userclient['client']['client_name'] + '</li>';
              }
            }
          });
          if(company != '')
            company += '</ul>';

          $.each(user['roles'], function (role_idx, role) {           
            if(dvuser)          
              user_datas.push({                             
                'id' : user['id'],
                'fake_id' : user_start,
                'name' : dvuser['firstname'] + " " + dvuser['lastname'],       
                'firstname' : dvuser['firstname'],           
                'lastname' : dvuser['lastname'],
                'email' : user['email'],
                'telephone' : (dvuser['telephone'] == null) ? '-' : dvuser['telephone'],
                'designation' : (dvuser['designation'] == null) ? '' : dvuser['designation'],
                'lang' : dvuser['lang'],
                'role' : role['name'],
                'status' : parseInt(dvuser['status']),
                'company' : company
              });
            else
              user_datas.push({                             
                'id' : user['id'],
                'fake_id' : user_start,
                'name' : user['name'],
                'firstname' : user['name'],
                'lastname' : '',
                'email' : user['email'],
                'telephone' : '-',
                'designation' : '',
                'lang' : 'en',
                'role' : role['name'],
                'status' : parseInt(0),
                'company' : company
              });
            user_start = user_start + 1;  
          });                  
        });

        return user_datas;
      }//user 
      else if(type == 'taskdate')
      {
        var taskdates = result['taskdates'];
        
        taskdate_datas = [];
        var taskdate_start = 1;

        //var currdate = moment().format("YYYY-MM");

        $.each(taskdates, function (idx, taskdate) {                       
          taskdate_datas.push({                             
            'id' : taskdate['id'],
            'fake_id' : taskdate_start,
            'taskname' : taskdate['task_name'],       
            //'task_date' : moment(currdate + '-' + taskdate['task_date']).format('Do') + " of every month",     
            'task_date' : taskdate['task_date'],  
            'task_description' : taskdate['task_description'],                       
            'status' : parseInt(taskdate['status'])
          });
          taskdate_start = taskdate_start + 1;                    
        });

        return taskdate_datas;
      }//taskdates
      else if(type == 'invoice')
      {
        var invoices = result['invoices'];
        
        var client_name = $('h4.breadcrumb-wrapper span a').html().toLowerCase();

        invoice_correct_datas = [];
        invoice_wrong_datas = [];
        invoice_managed_datas = [];
        var invoice_correct_start = 1;
        var invoice_wrong_start = 1;
        var invoice_managed_start = 1;
        var invoice_pdf = '';
        
        var client_api_name = $("#client_api_name").val();
        var client_currency_code = $("#currency_code").val();
        var client_country = $("#client_country").val();
        //var is_reverse = ($("#is_reverse").val() == '1') ? -1 : 1;
        
        if(client_currency_code == '')
        {
          if(client_country == "DK")
            client_currency_code = "DKK";      
          else if(client_country == "NO") 
           client_currency_code = "NOK";
          else if(client_country == "SE") 
            client_currency_code = "SEK";      
          else if(client_country == "GB")
            client_currency_code = "GBP";      
          else if(client_country == "IN")  
            client_currency_code = "INR";  
          else if(client_country == "FR") 
            client_currency_code = "EUR"; 
          else if(client_country == "CH") 
            client_currency_code = "CHF";       
        }

        $.each(invoices, function (idx, invoice) { 
          var tax_code = invoice['tax_code'];
          var invoice_type = invoice['invoice_type'];
          var invoice_vatpercentage = invoice['vat_rate'];
          var invoice_currency = (invoice['local_currency_code'] == null || invoice['local_currency_code'] == "") ? invoice['currency_code'] : invoice['local_currency_code'];
         //console.log(invoice_currency);
          var currency_locale = 'en-US';
          var currency_style = invoice_currency;
          if(invoice_currency == "DKK" || invoice_currency == "NOK")           
              currency_locale = 'da-DK';    
              //currency_style = 'DKK';         
          //else if(invoice_currency == "NOK")       
          //    currency_locale = 'no-NO';
          else if(invoice_currency == "SEK")       
              currency_locale = 'sv-SE';      
          else if(invoice_currency == "GBP")     
              currency_locale = 'en-GB';      
          else if(invoice_currency == "INR")        
              currency_locale = 'en-IN';
          else if(invoice_currency == "EUR")        
              currency_locale = 'fr-FR';
          else if(invoice_currency == "CHF")        
              currency_locale = 'fr-FR';      

          var currency_symbol = invoice['currency_code'];//currencySymbol(currency_locale, invoice_currency);

          var pushdata = customFilter(search_type, search_percentage, search_currency, invoice_type, invoice_vatpercentage, invoice_currency);
  //console.log(pushdata);
  //console.log(search_type + ", " + Number(search_percentage) + ", " + search_currency);
  //console.log(invoice_type + ", " + Number(invoice_vatpercentage) + ", " + invoice_currency);
          var local_currency_code = invoice['local_currency_code'];

          if(client_api_name == 'Dynamics 365')
            invoice_pdf = '-'; 
          else if(client_api_name == 'Dynamics 365 SmartApi')
            invoice_pdf = '-'; 
          else if(client_api_name == 'E-conomic')
          {  
            // if(is_reverse == -1)
            // {              
            //   if(tax_code.indexOf('_CN') !== -1)
            //     tax_code = tax_code.replace('_CN', '');
            //   else
            //     tax_code = tax_code + '_CN';
            // }

            if(client_name == "designbysi aps")
              invoice_pdf = '-'; 
            else
              invoice_pdf = '<span class="pdf text-primary form-check-label" target="_blank" data-vat_reg_id="'+ invoice['vat_reg_id'] + '" data-invoice_id="'+ invoice['invoice_no'] + '"><i class="fa fa-download"></i></span>';   
          }
          else if(client_api_name == 'Uniconta') 
            invoice_pdf = '-'; 
          else if(client_api_name == 'Shopify')  
            invoice_pdf = '-';
          else if(client_api_name == 'Billy')  
          {            
            if(invoice['invoice_id'])
              invoice_pdf = '<a href="'+ invoice['invoice_id'] + '" target="_blank"><i class="fa fa-download"></i></a>';
            else
              invoice_pdf = '-';     
          }
          else if(client_api_name == 'FTP' || client_api_name == null)
            invoice_pdf = '-';
          else
            invoice_pdf = '-';

          if(pushdata)
          {            
            //invoice_pdf = '<span class="pdf text-primary form-check-label" target="_blank" data-vat_reg_id="'+ invoice['vat_reg_id'] + '" data-invoice_id="'+ invoice['invoice_id'] + '"><i class="fa fa-download"></i></span>';  

            if(client_currency_code == invoice_currency)
            {              
              invoice_correct_datas.push({                 
                'id' : invoice['id'],   
                'fake_id' : invoice_correct_start, 
                'invoice_type' : invoice['invoice_type'],        
                'tax_code' : tax_code,//invoice['tax_code'],
                'invoice_date' : moment(invoice['invoice_date']).format('DD-MM-YYYY'),
                'acc_no' : invoice['acc_no'], 
                'invoice_no' : invoice['invoice_no'],
                'currency_code' : currency_symbol,
                'total_net' : new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(invoice['total_net']),
                'vat_rate' : Number(invoice['vat_rate']) + '%',
                'total_vat' : new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(invoice['total_vat']),
                'total_gross' : new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(invoice['total_gross']),
                'local_currency_code' : invoice['local_currency_code'],
    //             'exchange_rate' : new Intl.NumberFormat(currency_locale, {
    // style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(invoice['exchange_rate']),
                'exchange_rate' : new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 5, maximumFractionDigits: 5}).format(invoice['exchange_rate']),
                'local_total_net' : new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(invoice['local_total_net']),
                'local_total_vat' : new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(invoice['local_total_vat']),
                'local_total_gross' : new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(invoice['local_total_gross']),
                'n' : invoice['n'],
                'o' : invoice['o'],
                'p' : invoice['p'],
                'q' : invoice['q'],
                'c_name' : invoice['c_name'],
                'c_vat_no' : invoice['c_vat_no'],
                'c_street' : (invoice['c_street'] == null || invoice['c_street'] == "") ? '-' : invoice['c_street'].replace('/\r\n|\r|\n/g', ' '),
                'c_house_no' : (invoice['c_house_no'] == null || invoice['c_house_no'] == "") ? '-' : invoice['c_house_no'].replace('/\r\n|\r|\n/g', ' '),
                'c_city' : invoice['c_city'],
                'c_postcode' : invoice['c_postcode'],
                'c_country' : invoice['c_country'],
                'pdf' : invoice_pdf,
                'from_currency' : invoice_currency,

                'disregard_invoice': invoice['disregard_invoice'],
                'disregard_comment': invoice['disregard_comment']
              });
            }  
            else  
            { 
              invoice_wrong_datas.push({                 
                'id' : invoice['id'],   
                'fake_id' : invoice_wrong_start, 
                'invoice_type' : invoice['invoice_type'],        
                'tax_code' : tax_code,//invoice['tax_code'],
                'invoice_date' : moment(invoice['invoice_date']).format('DD-MM-YYYY'),
                'acc_no' : invoice['acc_no'], 
                'invoice_no' : invoice['invoice_no'],
                'currency_code' : currency_symbol,
                'total_net' : new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(invoice['total_net']),
                'vat_rate' : Number(invoice['vat_rate']) + '%',
                'total_vat' : new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(invoice['total_vat']),
                'total_gross' : new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(invoice['total_gross']),
                'local_currency_code' : invoice['local_currency_code'],
                'exchange_rate' : new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 5, maximumFractionDigits: 5}).format(invoice['exchange_rate']),
                'local_total_net' : new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(invoice['local_total_net']),
                'local_total_vat' : new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(invoice['local_total_vat']),
                'local_total_gross' : new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(invoice['local_total_gross']),
                'n' : invoice['n'],
                'o' : invoice['o'],
                'p' : invoice['p'],
                'q' : invoice['q'],
                'c_name' : invoice['c_name'],
                'c_vat_no' : invoice['c_vat_no'],
                'c_street' : (invoice['c_street'] == null || invoice['c_street'] == "") ? '-' : invoice['c_street'].replace('/\r\n|\r|\n/g', ' '),
                'c_house_no' : (invoice['c_house_no'] == null || invoice['c_house_no'] == "") ? '-' : invoice['c_house_no'].replace('/\r\n|\r|\n/g', ' '),
                'c_city' : invoice['c_city'],
                'c_postcode' : invoice['c_postcode'],
                'c_country' : invoice['c_country'],
                'pdf' : invoice_pdf,
                'from_currency' : invoice_currency,

                'disregard_invoice': invoice['disregard_invoice'],
                'disregard_comment': invoice['disregard_comment']
              });                 
            }  

            invoice_correct_start = invoice_correct_start + 1;
            invoice_wrong_start = invoice_wrong_start + 1;
            
          } 

          if(local_currency_code != "" && local_currency_code != null && (client_currency_code != invoice_currency))
          {
            invoice_managed_datas.push({                 
              'id' : invoice['id'],   
              'fake_id' : invoice_correct_start, 
              'invoice_type' : invoice['invoice_type'],        
              'tax_code' : tax_code,//invoice['tax_code'],
              'invoice_date' : moment(invoice['invoice_date']).format('DD-MM-YYYY'),
              'acc_no' : invoice['acc_no'], 
              'invoice_no' : invoice['invoice_no'],
              'currency_code' : currency_symbol,
              'total_net' : new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(invoice['total_net']),
              'vat_rate' : Number(invoice['vat_rate']) + '%',
              'total_vat' : new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(invoice['total_vat']),
              'total_gross' : new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(invoice['total_gross']),
              'local_currency_code' : invoice['local_currency_code'],
              'exchange_rate' : new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 5, maximumFractionDigits: 5}).format(invoice['exchange_rate']),
              'local_total_net' : new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(invoice['local_total_net']),
              'local_total_vat' : new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(invoice['local_total_vat']),
              'local_total_gross' : new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(invoice['local_total_gross']),
              'n' : invoice['n'],
              'o' : invoice['o'],
              'p' : invoice['p'],
              'q' : invoice['q'],
              'c_name' : invoice['c_name'],
              'c_vat_no' : invoice['c_vat_no'],
              'c_street' : (invoice['c_street'] == null || invoice['c_street'] == "") ? '-' : invoice['c_street'].replace('/\r\n|\r|\n/g', ' '),
              'c_house_no' : (invoice['c_house_no'] == null || invoice['c_house_no'] == "") ? '-' : invoice['c_house_no'].replace('/\r\n|\r|\n/g', ' '),
              'c_city' : invoice['c_city'],
              'c_postcode' : invoice['c_postcode'],
              'c_country' : invoice['c_country'],
              'pdf' : invoice_pdf,
              'from_currency' : invoice_currency,

              'disregard_invoice': invoice['disregard_invoice'],
              'disregard_comment': invoice['disregard_comment']         
            });
            invoice_managed_start = invoice_managed_start + 1;
          }                   
        });

        return {
          'invoice_correct_datas' : invoice_correct_datas, 
          'invoice_wrong_datas' : invoice_wrong_datas,
          'invoice_managed_datas' : invoice_managed_datas
        };
      }//invoice 
      else if(type == 'compliance')
      {
        var users = result['matched_users'];
        var cvrusers = result['matched_cvr_users'];

        matched_user_datas = [];
        var matched_user_start = 1;
        $.each(users, function (idx, user) { 
          var dvuser = user['dvuser'];
          var role = user['roles'][0];  
          var userclients = user['userclient'];   

          if(userclients.length == 0)
          {
            matched_user_datas.push({                             
              'id' : user['id'],
              'fake_id' : matched_user_start,
              'client_name' : '-', 
              'company_reg_no' : '-', 
              'name' : dvuser['firstname'] + " " + dvuser['lastname'],       
              'firstname' : dvuser['firstname'],           
              'lastname' : dvuser['lastname'],
              'email' : user['email'],
              'telephone' : (dvuser['telephone'] == null) ? '-' : dvuser['telephone'],
              'designation' : (dvuser['designation'] == null) ? '' : dvuser['designation'],
              'lang' : dvuser['lang'],
              'role' : role['name'],
              'status' : parseInt(dvuser['status']),
              'compliance_status' : parseInt(dvuser['compliance_status']),
              'excel_firstname' : dvuser['compliance_firstname'],     
              'excel_lastname' : dvuser['compliance_lastname'],     
              'excel_designation' : dvuser['compliance_designation']
            });
            matched_user_start = matched_user_start + 1;  
          }
          else
          {  
            $.each(userclients, function (idx1, userclient) {
              var client = userclient['client'];
              matched_user_datas.push({                             
                    'id' : user['id'],
                    'fake_id' : matched_user_start,
                    'client_name' : client['client_name'], 
                    'company_reg_no' : client['vatno'], 
                    'name' : dvuser['firstname'] + " " + dvuser['lastname'],       
                    'firstname' : dvuser['firstname'],           
                    'lastname' : dvuser['lastname'],
                    'email' : user['email'],
                    'telephone' : (dvuser['telephone'] == null) ? '-' : dvuser['telephone'],
                    'designation' : (dvuser['designation'] == null) ? '' : dvuser['designation'],
                    'lang' : dvuser['lang'],
                    'role' : role['name'],
                    'status' : parseInt(dvuser['status']),
                    'compliance_status' : parseInt(dvuser['compliance_status']),
                    'excel_firstname' : dvuser['compliance_firstname'],     
                    'excel_lastname' : dvuser['compliance_lastname'],     
                    'excel_designation' : dvuser['compliance_designation']
                  });
              matched_user_start = matched_user_start + 1;             
            });
          }                 
        });

        if(cvrusers != null)
        {
          $.each(cvrusers, function (idx, cvruser) { 
            var cvrclient = cvruser['client'];                       
              matched_user_datas.push({                             
                'id' : cvruser['id'],
                'fake_id' : matched_user_start,
                'client_name' : cvrclient['client_name'], 
                'company_reg_no' :  cvrclient['vatno'], 
                'name' : cvruser['person_name'],       
                'firstname' : (cvruser['person_name'] == null) ? '-' : cvruser['person_name'],           
                'lastname' : (cvruser['lastname'] == null) ? '-' : cvruser['lastname'],
                'email' : (cvruser['email'] == null) ? '-' : cvruser['email'],
                'telephone' : (cvruser['telephone'] == null) ? '-' : cvruser['telephone'],
                'designation' : (cvruser['person_designation'] == null) ? '' : cvruser['person_designation'],
                'lang' : (cvruser['lang'] == null) ? '' : cvruser['lang'],
                'role' : '-',
                'status' :'-',
                'compliance_status' : parseInt(cvruser['compliance_status']),
                'excel_firstname' : cvruser['compliance_firstname'],     
                'excel_lastname' : cvruser['compliance_lastname'],     
                'excel_designation' : cvruser['compliance_designation']
              });
              
              matched_user_start = matched_user_start + 1;                                  
          });
        }

        return matched_user_datas;
      }//compliance 
      else if(type == 'reminder')
      {
        var reminders = result['reminders'];
        var authUser = result['authUser']; 
        
        reminder_datas = [];
        var reminder_start = 1;

        var vatregmain = "";
        var client = "";
        var client_name ="-";
        var vat_country = "-";
        var vat_start_date = "-";
        var vat_general_periods = "-";

        $.each(reminders, function (idx, reminder) {
          var reminder_users = [];

          var reminderusers = reminder['reminderuser'];
          var reminderactionoption = reminder['reminderactionoption'];

          if(reminder['vatregmain'] != null)
          {
            vatregmain = reminder['vatregmain']; 
            vat_country = vatregmain['country'];
            vat_start_date = moment(vatregmain['service_start']).format('MMM Y');
            vat_general_periods = vatregmain['general_periods'];

            client = vatregmain['client']; 
            client_name =client['client_name'];
          }
          else
          {
            vat_country = "-";
            vat_start_date = "-";
            vat_general_periods = "-";
            client_name = "All";
          }

          if(reminder['reminderuser'].length == 0)
          {
            reminder_users.push({         
              'name' : authUser['firstname'] + " " + authUser['lastname'],                                          
              'firstname' : authUser['firstname'],           
              'lastname' : authUser['lastname'],
              'email' : authUser['email'],
              'role' : authUser['rolename']              
            });
          }
          else
          {
            $.each(reminderusers, function (idx1, reminderuser) {
              var user = reminderuser['user'];
              var dvuser = user['dvuser'];
              var role = user['roles'][0];       
              
              reminder_users.push({         
                'name' : dvuser['firstname'] + " " + dvuser['lastname'],                                          
                'firstname' : dvuser['firstname'],           
                'lastname' : dvuser['lastname'],
                'email' : user['email'],
                'role' : role['name']              
              }); 
            });
          }
                    
          // Sort keys: longest first
          const keys = Object.keys(periodmap).sort((a, b) => b.length - a.length);

          const regex = new RegExp(keys.join("|"), "g");

          let period_text = reminder['period'].replace(regex, m => periodmap[m])
                              .replace(/\b[a-z]/g, c => c.toUpperCase());             
          
          reminder_datas.push({                             
            'id' : reminder['id'],
            'fake_id' : reminder_start,
            'country' : reminder['reminder_country'],
            'reminder_template' : (reminder['reminder_template']) ? reminder['reminder_template'] : '',
            'title' : reminder['title'], 
            'users' : reminder_users,
            //'client' : client['client_name'],          
            //'vatregmain' : vatregmain['country'] + " " + moment(vatregmain['service_start']).format('MMM Y') + " " + vatregmain['general_periods'],
            'client' : client_name,
            'vatregmain' : vat_country + " " + vat_start_date + " " + vat_general_periods,
            'reminder_action' : reminderactionoption['action_name'],
            'schedule' : reminder['schedule'],
            'year' : reminder['year'],
            'period' : reminder['period'],
            'period_text' : (period_text) ? period_text : '-',
            'start_at' : moment(reminder['start_at']).format('DD-MM-YYYY hh:mm A'),
            'status' : parseInt(reminder['status'])
          });
          
          reminder_start = reminder_start + 1;                    
        });

        return reminder_datas;
      }//reminder 
      else if(type == 'reminderhistory')
      {
        var reminders = result['reminders'];
        var authUser = result['authUser']; 

        reminder_history_datas = [];
        var reminder_history_start = 1;

        var vatregmain = "";
        var client = "";
        var client_name ="-";
        var vat_country = "-";
        var vat_start_date = "-";
        var vat_general_periods = "-";

        $.each(reminders, function (idx, reminder) {
          var reminder_users = [];
          var reminder_histories = [];

          var reminderhistories = reminder['reminderhistory'];
          var reminderusers = reminder['reminderuser'];
          var reminderactionoption = reminder['reminderactionoption'];
          //var vatregmain = reminder['vatregmain']; 
          //var client = vatregmain['client']; 

          //vatregmain = (reminder['vatregmain'] == null) ? '' : reminder['vatregmain']; 
          //client = (vatregmain == null) ? '' : vatregmain['client']; 
          if(reminder['vatregmain'] != null)
          {
             vatregmain = reminder['vatregmain']; 
            vat_country = vatregmain['country'];
            vat_start_date = moment(vatregmain['service_start']).format('MMM Y');
            vat_general_periods = vatregmain['general_periods'];

            client = vatregmain['client']; 
            client_name =client['client_name'];
          }
          else
          {
            vat_country = "-";
            vat_start_date = "-";
            vat_general_periods = "-";
            client_name = "All";
          }

          $.each(reminderhistories, function (idx1, reminderhistory) {            
            reminder_histories.push({         
              'sent_at' : moment(reminderhistory['sent_at']).format('DD-MM-YYYY'),             
            }); 
          });

          if(reminder['reminderuser'].length == 0)
          {
            reminder_users.push({         
              'name' : authUser['firstname'] + " " + authUser['lastname'],                                          
              'firstname' : authUser['firstname'],           
              'lastname' : authUser['lastname'],
              'email' : authUser['email'],
              'role' : authUser['rolename']              
            }); 
          } 
          else
          {
            $.each(reminderusers, function (idx1, reminderuser) {
              var user = reminderuser['user'];
              var dvuser = user['dvuser'];
              var role = user['roles'][0];       
              
              reminder_users.push({         
                'name' : dvuser['firstname'] + " " + dvuser['lastname'],                                          
                'firstname' : dvuser['firstname'],           
                'lastname' : dvuser['lastname'],
                'email' : user['email'],
                'role' : role['name']              
              }); 
            });
          }
               
          if(reminder['reminderhistory'].length > 0)  
          {     
            // Sort keys: longest first
            const keys = Object.keys(periodmap).sort((a, b) => b.length - a.length);

            const regex = new RegExp(keys.join("|"), "g");

            let period_text = reminder['period'].replace(regex, m => periodmap[m])
                                .replace(/\b[a-z]/g, c => c.toUpperCase());

            reminder_history_datas.push({                             
              'id' : reminder['id'],
              'fake_id' : reminder_history_start,
              'country' : reminder['reminder_country'], 
              'reminder_template' : (reminder['reminder_template']) ? reminder['reminder_template'] : '',
              'title' : reminder['title'], 
              'users' : reminder_users,
              //'client' : client['client_name'],          
              //'vatregmain' : vatregmain['country'] + " " + moment(vatregmain['service_start']).format('MMM Y') + " " + vatregmain['general_periods'],
              'client' : client_name,            
              'vatregmain' : vat_country + " " + vat_start_date + " " + vat_general_periods,
              'reminder_action' : reminderactionoption['action_name'],
              'schedule' : reminder['schedule'],
              'year' : reminder['year'],
              'period' : reminder['period'],
              'period_text' : (period_text) ? period_text : '-',
              'start_at' : moment(reminder['start_at']).format('DD-MM-YYYY hh:mm A'),
              'status' : parseInt(reminder['status']),
              'histories' : reminder_histories,
            });
            
            reminder_history_start = reminder_history_start + 1;  
          }                  
        });

        return reminder_history_datas;
      }//reminder history
      else if(type == 'bulkupload')
      {
        var vatregs = result['vatregs'];
        
        bulkupload_datas = [];
        var bulkupload_start = 1;
        $.each(vatregs, function (idx, vatreg) { 
          var client = vatreg['client'];
          var clientusers = client['userclient'];
          var importvatfiles = vatreg['importvatfiles'];   
          
          var client_users = [];
          $.each(clientusers, function (idx1, clientuser) { 
            var user = clientuser['user'];
            var dvuser = user['dvuser'];

            if(!dvuser['is_deleted'])
            {
              client_users.push({                                                 
                'id' : user['id'],
                'name' : dvuser['firstname'] + " " + dvuser['lastname'],
                'firstname' : dvuser['firstname'],
                'lastname' :  dvuser['lastname'],
                'email' : user['email'],
                'notificationsettings' : user['notificationsettings']
              });
            }
          });

          var is_bulk = 0;
          var importvat_files = [];
          $.each(importvatfiles, function (idx1, importvatfile) {                
            if(importvatfile['upload_type'] == 'bulk' && importvatfile['send_email'] == 0)
            {
              is_bulk = 1;
            

              importvat_files.push({                                                 
                'id' : importvatfile['id'],
                'file_id' : importvatfile['file_id'],
                'file_type' : importvatfile['file_type'],
                'month_year' :  importvatfile['month_year'] 
              });
            }
          });

          if(is_bulk)
          {               
            bulkupload_datas.push({   
              'vat_reg_id' : vatreg['id'],
              'client_id' : client['id'],      
              'fake_id' : bulkupload_start,
              'client_name' : client['client_name'],
              'vat_period' : moment(vatreg['service_start']).format('MMM Y') + '-' + moment(vatreg['service_start']).add(1, 'month').format('MMM Y'),
              'users' : client_users,
              'files' : importvat_files             
            });
            bulkupload_start = bulkupload_start + 1;                           
          }
        });
          
        return bulkupload_datas;
      }//bulkupload
      else if(type == 'clientconnection')
      {               
        var clientconnections = result['clientconnection'];

        connection_datas = [];

        var connection_start = 1;

        $.each(clientconnections, function (idx, clientconnection) { 
          var client_name = clientconnection['client_name'];
          var clientapi = clientconnection['clientapi'];
          var vatregmains = clientconnection['vatregmain'];  
          var altconnection_name = "";
          var subsconnection_name = "";
        
          if(vatregmains.length == 0)
          {
            $.each(clientapi, function (idx1, clientapi) {
              if(clientapi['connection_name'] == null)        
                altconnection_name = clientconnection['client_name'] + "- " + clientapi['api_name'];        
              else                
                altconnection_name = clientapi['connection_name']; 
             
              connection_datas.push({                             
                'id' : clientconnection['id'],
                'fake_id' : connection_start,
                'client_name' : clientconnection['client_name'],              
                'connection_name' : altconnection_name,
                'connection_status' : clientapi['connection_status'],                  
                'status' : clientapi['status'] ,              
                'connection_remarks' : clientapi['connection_remarks']
              });
              connection_start = connection_start + 1;
            });
          }
          else
          {              
            $.each(clientapi, function (idx2, clientapi) {
              var altconnection_name = "";
              var connection_status = "";
              var connection_remarks = "";
              var client_status = "";
              
              connection_status = clientapi['connection_status'];
              client_status = clientapi['status'];
              connection_remarks = clientapi['connection_remarks'];
              if(clientapi['vat_reg_main_id'] == null)          
                altconnection_name = clientapi['connection_name'];
              else
              {
                var vat_ids = false;
                $.each(vatregmains, function (idx1, vatregmain) {
                  if(vatregmain['id'] == clientapi['vat_reg_main_id'])             
                    vat_ids = true;
                  
                  if(vat_ids)              
                    altconnection_name = clientconnection['client_name'] + '-' + vatregmain['country'] + '-'   + clientapi['api_name'];
                });
              }

              connection_datas.push({                             
                'id' : clientconnection['id'],
                'fake_id' : connection_start,
                'client_name' : clientconnection['client_name'],              
                'connection_name' : altconnection_name,
                'connection_status' : connection_status,                  
                'status' : client_status ,              
                'connection_remarks' : connection_remarks
              });
              connection_start = connection_start + 1;                    
            });             
          } 
        }); 

        //var vatregindex =0;
        // $.each(clientconnections, function (idx, clientconnection) {
        //   var client_name = clientconnection['client_name'];
        //   var clientapi = clientconnection['clientapi'];
        //   var vatregmain = clientconnection['vatregmain'];

        //   // getting datas from the clientapi array
        //   var client_id = clientapi['client_id'];
        //   var  connection_name = clientapi[' connection_name'];
        //   var  connection_status = clientapi[' connection_status'];
        //   var  connection_remarks = clientapi[' connection_remarks'];  

        //   //end getting datas from the clientapi array
        //   var vatregmain_datas = [];

        //   // looping vatregmain for the client
        //   $.each(vatregmain, function (idx1, vatregmain) {
        //     var altconnection_name = "";
        //     var subsconnection_name = "";

        //     if(clientapi['connection_name'] == null)        
        //     altconnection_name = clientconnection['client_name'] + '-' + vatregmain['country'] + "- " + clientapi['api_name'];        
        //     else                
        //     altconnection_name = clientapi['connection_name'];
         
        //     connection_datas.push({                             
        //       'id' : clientconnection['id'],
        //       'fake_id' : connection_start,
        //       'client_name' : clientconnection['client_name'],              
        //       'connection_name' : altconnection_name,
        //       'connection_status' : clientapi['connection_status'],                  
        //       'status' : clientapi['status'] ,              
        //       'connection_remarks' : clientapi['connection_remarks']            
        //     });
        //   });
        //   // end looping vatregmain for the client
        //   connection_start = connection_start + 1; 
        // });         
        return connection_datas;
      }//new connection
      // else if(type == 'excelcolumntemplate')
      // {
      //   var excelcolumntemplates = result['excelcolumntemplates'];
        
      //   excelcolumntemplate_datas = [];
      //   var excelcolumntemplate_start = 1;
        
      //   $.each(excelcolumntemplates, function (idx, excelcolumntemplate) {                       
      //     excelcolumntemplate_datas.push({                             
      //       'id' : excelcolumntemplate['id'],
      //       'fake_id' : excelcolumntemplate_start,
      //       'name' : excelcolumntemplate['name'],                   
      //       'columns' : excelcolumntemplate['columns'],  
      //       'version' : parseInt(excelcolumntemplate['version']),                    
      //       'status' : parseInt(excelcolumntemplate['status']),
      //       'vatreg' : excelcolumntemplate['vatreg']
      //     });
      //     excelcolumntemplate_start = excelcolumntemplate_start + 1;                    
      //   });

      //   return excelcolumntemplate_datas;
      // }//excelcolumntemplate
      else if(type == 'anyexcel_template')
      {
        var anyexcel_templates = result['anyexcel_templates'];
        
        anyexcel_template_datas = [];
        var anyexcel_template_start = 1;
        
        $.each(anyexcel_templates, function (idx, anyexcel_template) {                       
          anyexcel_template_datas.push({                             
            'id' : anyexcel_template['id'],
            'fake_id' : anyexcel_template_start,
            'name' : anyexcel_template['name'], 
            'client_name' : anyexcel_template['client']['client_name'],                  
            'columns' : anyexcel_template['columns'],  
            'version' : parseInt(anyexcel_template['version']),                    
            'status' : parseInt(anyexcel_template['status']),
            //'vatreg' : anyexcel_template['vatreg'],
            'vatreturnfiles' : anyexcel_template['vatreturnfiles']
          });
          anyexcel_template_start = anyexcel_template_start + 1;                    
        });

        return anyexcel_template_datas;
      }//anyexcel_template
      else if(type == 'declaration')
      {   
        var declarations = result['declarations'];

        var other_period_importreconciliationcominvoices = declarations['other_period_importreconciliationcominvoices'];
      
        var vatregmain = declarations['vatregmain'];
        var vatreturns = declarations['vatreturns'];
        var client = declarations['client'];
      
        var importreconciliationfiles = declarations['importreconciliationfiles'];
        
        var importreconciliationcominvoices = declarations['importreconciliationcominvoices'];
        var importreconciliationsalesinvoices = declarations['importreconciliationsalesinvoices'];
      
        var importvatfiles = declarations['importvatfiles'];
        var importreconciliationswissfiles = declarations['importreconciliationswissfiles'];
      
        var currency_locale = 'da-DK';
        var currency_style = 'NOK';
        let vat_percent = 0.25;
        let add_month = 1;

        declaration_first_datas = [];
        declaration_second_datas = [];
      
        //var declaration_first_start = 1;
        //var declaration_second_start = 1;  

        if(vatregmain['country'] == 'CH')
        {
          currency_locale = 'fr-FR';
          currency_style = 'CHF';
          vat_percent = 0.081;
          add_month = 2;

          declaration_third_datas = [];
          var declaration_third_start = 1;  
        }              
        
        var org_no = (vatregmain['org_no']) ? vatregmain['org_no'] : '-';
      
        //var start_month_year = moment(declarations['service_start']).format('MM-Y');
        //var end_month_year = moment(declarations['service_start']).add(add_month, 'month').format('MM-Y');
     
        var total_com_invoice_net_amount = [];

        var total_vat_amount = [];
        var total_net_amount = [];    

        var modal_cominvoices = [];
        var sub_cominvoices = [];
       
        let arr_lope_no = [];

        $.each(importreconciliationcominvoices, function (idx, cominvoice) {

          var com_invoice_date = (cominvoice['month_year']) ? cominvoice['month_year'] : moment(cominvoice['invoice_date']).format('MM-Y');

          /*Disallow MA - EkspTypeNavn*/
          var allow = true;
          if(cominvoice['category_type'] == 'MA' || cominvoice['category_type'] == 'FU')
          {
            var filter_importvatfile_xml_datas = importvatfiles.filter(function(importvatfile) {                                        
                return (importvatfile.month_year === com_invoice_date && importvatfile.file_type === "xml");
            });
  
            if(filter_importvatfile_xml_datas.length > 0)
            {
              var filter_importvatfile_xml = filter_importvatfile_xml_datas[0]['xml'].filter(function(xml_data) { 
                var expo_type = xml_data['Ekspedisjon']['EkspType']['EkspTypeNavn'];                 
                var xml_lope_no = xml_data['Ekspedisjon']['EkspedisjonsId']['LopeNr'];                                               
                return (cominvoice['lope_no'] == xml_lope_no && expo_type.toLowerCase().indexOf('utførsel') !== -1);                
              });
  
              if(filter_importvatfile_xml.length > 0)
              {
                allow = false;

                if(cominvoice['data_from'] == 'ivf')
                {
                  if(com_invoice_date in modal_cominvoices)   
                  {}
                  else
                    modal_cominvoices[com_invoice_date] = [];

                  var filter_rematch_com_invoice_id = importreconciliationcominvoices.filter(function(obj) {                            
                    return (obj.rematch_com_invoice_id === cominvoice['id']);
                  });
                  filter_rematch_com_invoice_id_count = filter_rematch_com_invoice_id.length;

                  modal_cominvoices[com_invoice_date].push(
                    {
                      "id": cominvoice['id'],                    
                      "co_invoice_no": cominvoice['invoice_no'],
                      "disabled": (filter_rematch_com_invoice_id_count == 0) ? false : true,
                      "other_period": false
                    } 
                  );
                } //IVF
              }
            }
          }

          //if(cominvoice['category_type'] != 'MA' && cominvoice['category_type'] != 'SO')
          if(cominvoice['category_type'] != 'SO' && allow)
          {  
            //var com_invoice_date = (cominvoice['month_year']) ? cominvoice['month_year'] : moment(cominvoice['invoice_date']).format('MM-Y');
            var filter_rematch_com_invoice_id_count = 0;
            var filter_other_period_rematch_com_invoice_id_count = 0;            

            if(cominvoice['data_from'] == 'azure' || cominvoice['data_from'] == 'global-search-refresh' 
               || cominvoice['data_from'] == 'specific-global-search-refresh' 
               || cominvoice['data_from'] == 'specific-invoice-global-search-refresh'
               || cominvoice['data_from'] == 'cron'
               || cominvoice['data_from'] == 'ocr'
               || cominvoice['data_from'] == 'ocr-search-refresh' 
               || cominvoice['data_from'] == 'specific-ocr-search-refresh' 
               || cominvoice['data_from'] == 'specific-invoice-ocr-search-refresh')  
            {            
              if(com_invoice_date in modal_cominvoices)   
              {}
              else
                modal_cominvoices[com_invoice_date] = [];

              var filter_rematch_com_invoice_id = importreconciliationcominvoices.filter(function(obj) {                            
                return (obj.rematch_com_invoice_id === cominvoice['id']);
              });
              filter_rematch_com_invoice_id_count = filter_rematch_com_invoice_id.length;

              modal_cominvoices[com_invoice_date].push(
                {
                  "id": cominvoice['id'],                    
                  "co_invoice_no": cominvoice['invoice_no'],
                  "disabled": (filter_rematch_com_invoice_id_count == 0) ? false : true,
                  "other_period": false
                } 
              );
            }                      
              
            if(filter_rematch_com_invoice_id_count == 0)  
            {
              if(com_invoice_date in sub_cominvoices)   
              {}
              else
                sub_cominvoices[com_invoice_date] = [];

              var rematch_com_invoice_no = '-';
              var com_net_amount = (cominvoice['net_amount']) ? ((cominvoice['category_type'] == 'RE') ? 0 : cominvoice['net_amount']) : 0;
              //var com_net_amount = (cominvoice['net_amount']) ? cominvoice['net_amount'] : 0;

              var convert_currency_code = (cominvoice['convert_currency_code']) ? cominvoice['convert_currency_code'] : null;
              var convert_net_amount = (cominvoice['convert_net_amount']) ? cominvoice['convert_net_amount'] : null;
              var convert_vat_amount = (cominvoice['convert_vat_amount']) ? cominvoice['convert_vat_amount'] : null;
              var convert_total_amount = (cominvoice['convert_total_amount']) ? cominvoice['convert_total_amount'] : null;
              var exchange_rate = (cominvoice['exchange_rate']) ? cominvoice['exchange_rate'] : null;

              if(cominvoice['rematch_com_invoice_id'])
              {
                var filter_rematch_com_invoice_id = importreconciliationcominvoices.filter(function(obj) {                
                    return (obj.id === cominvoice['rematch_com_invoice_id']);
                });
               
                if(filter_rematch_com_invoice_id.length > 0)
                {
                  rematch_com_invoice_no = filter_rematch_com_invoice_id[0]['invoice_no'];              
                  com_net_amount = (cominvoice['disregard_invoice']) ? 0 : ((cominvoice['category_type'] == 'RE') ? 0 : filter_rematch_com_invoice_id[0]['net_amount']);       
                  //com_net_amount = (cominvoice['disregard_invoice']) ? 0 : filter_rematch_com_invoice_id[0]['net_amount'];       

                  convert_currency_code = (filter_rematch_com_invoice_id[0]['convert_currency_code']) ? filter_rematch_com_invoice_id[0]['convert_currency_code'] : null;
                  convert_net_amount = (filter_rematch_com_invoice_id[0]['convert_net_amount']) ? filter_rematch_com_invoice_id[0]['convert_net_amount'] : null;
                  convert_vat_amount = (filter_rematch_com_invoice_id[0]['convert_vat_amount']) ? filter_rematch_com_invoice_id[0]['convert_vat_amount'] : null;
                  convert_total_amount = (filter_rematch_com_invoice_id[0]['convert_total_amount']) ? filter_rematch_com_invoice_id[0]['convert_total_amount'] : null;
                  exchange_rate = (filter_rematch_com_invoice_id[0]['exchange_rate']) ? filter_rematch_com_invoice_id[0]['exchange_rate'] : null; 
                }
              }
              
              var net_amount_co_invoice = 0;
              if(cominvoice['omr_kurs'])
              {
                if(cominvoice['omr_kurs'] == 100)                    
                  net_amount_co_invoice = cominvoice['ivf_net_amount'];            
                else 
                {                             
                  let currency_value = cominvoice['currency_code'];  
                  
                  let omr_kurs_value = 1;
                  if(currency_value == 'DKK')   
                  {  
                    omr_kurs_value = cominvoice['omr_kurs'].replace(/[,.]/g, ""); 
                    omr_kurs_value = omr_kurs_value.substr(0, 1) + "." + omr_kurs_value.substr(1);
                  }
                  else if(currency_value == 'USD' || currency_value == 'EUR')   
                  {
                    omr_kurs_value = cominvoice['omr_kurs'].replace(/[,.]/g, ""); 
                    omr_kurs_value = omr_kurs_value.substr(0, 2) + "." + omr_kurs_value.substr(2);
                  }

                  net_amount_co_invoice = cominvoice['ivf_net_amount'] * omr_kurs_value;
                }
              }
              else
                net_amount_co_invoice = cominvoice['net_amount']; 

              if(cominvoice['category_type'] == 'RE')
              {                
                if (cominvoice['statistical_value'].startsWith("-")) 
                  //net_amount_co_invoice = '-' + net_amount_co_invoice;
                  net_amount_co_invoice = -1 * net_amount_co_invoice;
                else
                {                                
                  let parsed_stat_val =  parseAmountValue(cominvoice['statistical_value'], cominvoice['currency']);
                  if (parsed_stat_val == 0)       
                    net_amount_co_invoice = 0;                  
                }
              } //Refund
              else if(cominvoice['category_type'] == 'EB')
              {             
                net_amount_co_invoice = cominvoice['statistical_value'];
              } //Recalculation

              var final_com_invoice_no = (cominvoice['rematch_com_invoice_id']) ? rematch_com_invoice_no : cominvoice['invoice_no'];

              /* Cargo File */
              var cargo_file_id = "";    
              if(vatregmain['country'] == 'CH')
              {
                var filter_cargo_pdf = importreconciliationswissfiles.filter(function(obj) {   
                  if(obj.invoice_no)
                    return ((obj.o_file_name.indexOf(cominvoice['lope_no']) !== -1) && 
                      (obj.invoice_no.indexOf(final_com_invoice_no) !== -1 || final_com_invoice_no.indexOf(obj.invoice_no) !== -1 ||
                        obj.invoice_no.indexOf(cominvoice['invoice_no']) !== -1 || cominvoice['invoice_no'].indexOf(obj.invoice_no) !== -1));   
                  else
                    return (obj.o_file_name.indexOf(cominvoice['lope_no']) !== -1); 
                });

                if(filter_cargo_pdf.length > 0)
                  cargo_file_id = filter_cargo_pdf[0]['id'];
              } 
              else
              {
                var filter_importvatfile = importvatfiles.filter(function(importvatfile) {                                        
                    return (importvatfile.month_year === com_invoice_date && importvatfile.file_type === "xml");
                });
            
                if(filter_importvatfile.length > 0)
                {
                  var filter_cargo_pdf = filter_importvatfile[0]['cargodeclarationfiles'].filter(function(obj) {   
                    if(obj.cargo_com_invoice_nos)                                     
                      return (obj.run_no === cominvoice['lope_no'] && (obj.cargo_com_invoice_nos.indexOf(final_com_invoice_no) !== -1));
                    else
                      return false;
                  });
                  if(filter_cargo_pdf.length > 0)
                    cargo_file_id = filter_cargo_pdf[0]['id'];
                }
              }
              /* Cargo File */
              
              /* Group same lope no. - but NOT with split */              
              var already_lope_no_exist = false;                            
              if(cominvoice['lope_no'])
              {
                if(cominvoice['expo_no'])
                {
                  if (!arr_lope_no.includes(cominvoice['expo_no'] + cominvoice['lope_no']))                  
                  {               
                    arr_lope_no.push(cominvoice['expo_no'] + cominvoice['lope_no']);                    
                  }
                  else
                  {
                    if(cominvoice['no_of_split'] && cominvoice['rematch_com_invoice_id'])
                      already_lope_no_exist = true;
                  }
                }
              }              
              /* Group same lope no. - but NOT with split */

              if(already_lope_no_exist)
              {                              
                var index = sub_cominvoices[com_invoice_date].findIndex(element => (element.expo_no === cominvoice['expo_no'] && element.lope_no === cominvoice['lope_no']));

                if(index >= 0)
                {    
                  if(!cominvoice['disregard_invoice']) 
                  {             
                    var prev_com_invoice_no = sub_cominvoices[com_invoice_date][index]["co_invoice_no"];
                    sub_cominvoices[com_invoice_date][index]["co_invoice_no"] = prev_com_invoice_no + ', ' + final_com_invoice_no;
  
                    var prev_o_com_invoice_no = sub_cominvoices[com_invoice_date][index]["orginal_co_invoice_no"];
                    if(prev_o_com_invoice_no != cominvoice['invoice_no'])
                      sub_cominvoices[com_invoice_date][index]["orginal_co_invoice_no"] = prev_o_com_invoice_no + ', ' + cominvoice['invoice_no'];
  
                    var prev_com_invoice_id = sub_cominvoices[com_invoice_date][index]["group_lope_no"];
                    sub_cominvoices[com_invoice_date][index]["group_lope_no"] = prev_com_invoice_id + '***' + cominvoice['id'];
                  }

                  var prev_currency_code = sub_cominvoices[com_invoice_date][index]["currency"];

                  var prev_com_net_amount = sub_cominvoices[com_invoice_date][index]["com_net_amount"];                  
                  let parsed_cominvoice_net_amount =  parseAmountValue(prev_com_net_amount, prev_currency_code);   

                  if(parsed_cominvoice_net_amount == com_net_amount)        
                  {  
                  }
                  else
                  {    
                    let gross_com_net_amount = parseFloat(parsed_cominvoice_net_amount) + parseFloat(com_net_amount);
                    
                    sub_cominvoices[com_invoice_date][index]["com_net_amount"] = new Intl.NumberFormat(currency_locale, {
            style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(gross_com_net_amount);
                  }
                }
              }//lope no already exists
              else
              {  
                let country_vat_amount = 0;
                if(vatregmain['country'] == 'NO')
                {
                  country_vat_amount = new Intl.NumberFormat(currency_locale, {
          style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format((net_amount_co_invoice * vat_percent));
                }
                else if(vatregmain['country'] == 'CH')
                {
                  country_vat_amount = new Intl.NumberFormat(currency_locale, {
          style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(cominvoice['vat_amount']);
                }

                var index = sub_cominvoices[com_invoice_date].push(
                    {
                      "id": cominvoice['id'],
                      "country": vatregmain['country'],
                      "pdf": cargo_file_id,
                      "co_invoice_no": (cominvoice['disregard_type']) ? final_com_invoice_no : ((cominvoice['disregard_invoice']) ? '-' : final_com_invoice_no),     
                      "orginal_co_invoice_no": cominvoice['invoice_no'],
                      "co_invoice_date": moment(cominvoice['invoice_date']).format('DD-MM-YYYY'),
                      "expo_no": (cominvoice['expo_no']) ? cominvoice['expo_no'] : '-',   
                      "lope_no": (cominvoice['lope_no']) ? ((cominvoice['disregard_type'] == 'lopeno') ? '-' : cominvoice['lope_no']) : '-',     
                      "duties": (cominvoice['duties']) ? new Intl.NumberFormat(currency_locale, {
          style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(cominvoice['duties']) : new Intl.NumberFormat(currency_locale, {
          style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0),
                      "vat_on_duties": new Intl.NumberFormat(currency_locale, {
          style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format((cominvoice['duties'] * vat_percent)),
                      "adjustment": (cominvoice['adjustment']) ? new Intl.NumberFormat(currency_locale, {
          style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(cominvoice['adjustment']) : new Intl.NumberFormat(currency_locale, {
          style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0),
                      "vat_on_adjustment": new Intl.NumberFormat(currency_locale, {
          style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format((cominvoice['adjustment'] * vat_percent)),
                      "statistical_value": (cominvoice['statistical_value']) ? new Intl.NumberFormat(currency_locale, {                
          style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(cominvoice['statistical_value']) : new Intl.NumberFormat(currency_locale, {
          style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0),
                      "category_type": cominvoice['category_type'],
                      "category_desc": (cominvoice['category_desc']) ? cominvoice['category_desc'] : '',
                      "o_invoice_date": cominvoice['invoice_date'],
                      "ivf_net_amount": (cominvoice['ivf_net_amount']) ? new Intl.NumberFormat(currency_locale, {
          style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(cominvoice['ivf_net_amount']) : new Intl.NumberFormat(currency_locale, {
          style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0),
                      "com_net_amount": new Intl.NumberFormat(currency_locale, {
          style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(com_net_amount),
                      "net_amount_co_invoice": new Intl.NumberFormat(currency_locale, {
          style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(net_amount_co_invoice),
                      "import_vat": country_vat_amount,                
                      "currency": cominvoice['currency_code'],
                      "doc_status": cominvoice['doc_status'], 
                      "unmatch": cominvoice['unmatch'], 
                      "rematch_com_invoice_id": cominvoice['rematch_com_invoice_id'],
                      "doc_id": cominvoice['doc_id'],

                      "convert_currency_code": convert_currency_code,
                      "convert_net_amount": (convert_net_amount) ? new Intl.NumberFormat(currency_locale, {
          style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(convert_net_amount) : null,
                      "convert_vat_amount": (convert_vat_amount) ? new Intl.NumberFormat(currency_locale, {
          style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(convert_vat_amount) : null,
                      "convert_total_amount": (convert_total_amount) ? new Intl.NumberFormat(currency_locale, {
          style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(convert_total_amount) : null,
                      "exchange_rate": exchange_rate,

                      "disregard_invoice": cominvoice['disregard_invoice'],
                      "disregard_type": cominvoice['disregard_type'],
                      "disregarded_no": (cominvoice['disregard_type'] == 'lopeno') ? cominvoice['lope_no'] : ((cominvoice['disregard_invoice']) ? final_com_invoice_no : ''),
                      "disregard_reason": cominvoice['disregard_reason'],
                      "disregard_comment": cominvoice['disregard_comment'],
                      "disregard_comment_visiblity": cominvoice['disregard_comment_visiblity'],

                      "comment_reason": cominvoice['comment_reason'],
                      "comment": cominvoice['comment'],
                      "comment_visiblity": cominvoice['comment_visiblity'],
                      "no_of_split": cominvoice['no_of_split'],
                      "group_lope_no": cominvoice['id']
                    } 
                  ); 
                }//else 
            
                if(cominvoice['currency_code'] == 'NOK')
                {             
                  if(com_invoice_date in total_com_invoice_net_amount)            
                    total_com_invoice_net_amount[com_invoice_date] += parseFloat(net_amount_co_invoice);
                  else
                    total_com_invoice_net_amount[com_invoice_date] = parseFloat(net_amount_co_invoice);
                } 
           
                var sub_invoices = [];
                $.each(importreconciliationsalesinvoices, function (idx1, invoice) {
                     
                  if((cominvoice['rematch_com_invoice_id'] == invoice['com_invoice_id'] || cominvoice['id'] == invoice['com_invoice_id'])
                    && (cominvoice['category_type'] != 'RE') )
                  //if(cominvoice['rematch_com_invoice_id'] == invoice['com_invoice_id'] || cominvoice['id'] == invoice['com_invoice_id'])
                  {
                    var invoice_date = moment(invoice['invoice_date']).format('MM-Y');
                                       
                    // if(!invoice['disregard_invoice'])
                    // {                         
                        
                      var credit_note_symbol = '';
                      if(parseInt(invoice['credit_note']) == 1)
                        credit_note_symbol = '-';

                      var edit_from = "xml";                      
                      var filter_sales_pdf = importreconciliationfiles.filter(function(obj) {                                        
                          return (obj.invoice_no === invoice['invoice_no']);
                      });

                      var sales_xml_id = null;

                      var sales_net_amount = (invoice['convert_net_amount']) ? invoice['convert_net_amount'] : invoice['net_amount'];                      
                      var sales_shipping = invoice['shipping'];
                      var sales_variance = invoice['variance'];
                      var sales_vat_amount = (invoice['convert_vat_amount']) ? invoice['convert_vat_amount'] : invoice['vat_amount'];
                      var sales_adjustment_amount = invoice['adjustment_amount'];
                      var sales_currency = (invoice['convert_currency_code']) ? invoice['convert_currency_code'] : invoice['currency_code'];

                      if(filter_sales_pdf.length > 0)
                      {
                        sales_xml_id = filter_sales_pdf[0]['id'];

                        if(filter_sales_pdf[0]['salesinvoicesdata'])
                        {
                          edit_from = filter_sales_pdf[0]['salesinvoicesdata']['id'];
                          
                          //Extract converted amount from note
                          var tax_total_net_amount = 0;
                          var tax_total_amount = 0;
                          var tax_total_amount_currency_code = '';
                          if(filter_sales_pdf[0]['salesinvoicesdata']['currency_code'] != 'NOK')
                          {
                            var footer_note = $.trim(filter_sales_pdf[0]['salesinvoicesdata']['converted_note']);                              
                            if(footer_note.indexOf(' alt ') != -1)  
                            { 
                              let arr_footer_note = footer_note.split(" ");

                              // Find indexes where value is 'NOK'
                              let indexes = arr_footer_note
                                  .map((val, i) => val === 'NOK' ? i : -1)
                                  .filter(i => i !== -1);

                              // Get and normalize the next values
                              let amountsAfterNOK = indexes.map(i => {
                                  let val = arr_footer_note[i + 1];
                                  if (val === undefined) return null;

                                  return val
                                      .toString()
                                      .replace(/\./g, '')
                                      .replace(/'/g, '')
                                      .replace(/,/g, '.');
                              });

                              tax_total_net_amount = amountsAfterNOK[0];
                              tax_total_amount = amountsAfterNOK[1];
                              tax_total_amount_currency_code = 'NOK';
                            }
                          }//if not NOK
                          //Extract converted amount from note

                          if(filter_sales_pdf[0]['salesinvoicesdata']['note'])
                          {
                            sales_net_amount = filter_sales_pdf[0]['salesinvoicesdata']['tax_total_net_amount'];
                            //sales_shipping = filter_sales_pdf[0]['salesinvoicesdata']['shipping'];
                            sales_vat_amount = filter_sales_pdf[0]['salesinvoicesdata']['tax_total_amount'];
                            sales_currency = filter_sales_pdf[0]['salesinvoicesdata']['tax_total_amount_currency_code'];  

                            sales_variance = filter_sales_pdf[0]['salesinvoicesdata']['allowance_charge'];                                                  
                          }
                          else
                          {
                            if(filter_sales_pdf[0]['salesinvoicesdata']['currency_code'] != 'NOK')
                            {
                              sales_net_amount = tax_total_net_amount;                          
                              sales_vat_amount = tax_total_amount;
                              sales_currency = tax_total_amount_currency_code;
                            }
                            else
                            {
                              sales_net_amount = filter_sales_pdf[0]['salesinvoicesdata']['tax_total_net_amount'];                             
                              sales_vat_amount = filter_sales_pdf[0]['salesinvoicesdata']['tax_total_amount'];
                              sales_currency = filter_sales_pdf[0]['salesinvoicesdata']['tax_total_amount_currency_code'];

                              sales_variance = filter_sales_pdf[0]['salesinvoicesdata']['allowance_charge'];  
                            }
                          }
                        }
                      }

                      var vat_check_25 = 0;
                      if(sales_net_amount > 0)
                      {  
                        var invoice_shipping = (sales_shipping) ? parseFloat(sales_shipping) : 0;
                        var invoice_variance = (sales_variance) ? parseFloat(sales_variance) : 0;
                        var invoice_net_amount = (sales_net_amount) ? parseFloat(sales_net_amount) : 0;
                        var invoice_vat_amount = (sales_vat_amount) ? parseFloat(sales_vat_amount) : 0;
                        var invoice_adjustment_amount = (sales_adjustment_amount) ? parseFloat(sales_adjustment_amount) : 0;

                        var invoice_vat_percent = (invoice_vat_amount/((invoice_net_amount + invoice_shipping + invoice_variance) - invoice_adjustment_amount)) * 100;

                        var minimum_fraction_digits = (vatregmain['country'] == 'CH') ? 1 : 0;
                       
                        vat_check_25 = new Intl.NumberFormat(currency_locale, {
        style: 'decimal', currency: currency_style, minimumFractionDigits: minimum_fraction_digits, maximumFractionDigits: minimum_fraction_digits}).format(invoice_vat_percent);      
        //                 vat_check_25 = new Intl.NumberFormat(currency_locale, {
        // style: 'decimal', currency: currency_style, minimumFractionDigits: 0, maximumFractionDigits: 0}).format(((invoice_vat_amount/(invoice_net_amount + invoice_shipping)) * 100));      
                      }

                      sub_invoices.push(
                        {
                          "id": invoice['id'],
                          "pdf": sales_xml_id,
                          "edit_from": edit_from,                          
                          "invoice_no": invoice['invoice_no'],
                          "invoice_date": moment(invoice['invoice_date']).format('DD-MM-YYYY'),
                          "o_invoice_date": invoice['invoice_date'],
                          "is_net_amount_null": (sales_net_amount) ? false : true,
                          "net_amount": ((sales_net_amount == 0) ? '' : credit_note_symbol) + (sales_net_amount ? new Intl.NumberFormat(currency_locale, {
        style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(sales_net_amount) : new Intl.NumberFormat(currency_locale, {
        style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0)),
                          "shipping": ((sales_shipping == 0) ? '' : credit_note_symbol) + (sales_shipping ? new Intl.NumberFormat(currency_locale, {
        style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(sales_shipping) : new Intl.NumberFormat(currency_locale, {
        style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0)),
                          "variance": ((sales_variance == 0) ? '' : credit_note_symbol) + (sales_variance ? new Intl.NumberFormat(currency_locale, {
        style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(sales_variance) : new Intl.NumberFormat(currency_locale, {
        style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0)),
                          "vat_amount": ((sales_vat_amount == 0) ? '' : credit_note_symbol) + (sales_vat_amount ? new Intl.NumberFormat(currency_locale, {
        style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(sales_vat_amount) : new Intl.NumberFormat(currency_locale, {
        style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0)),
                          "adjustment_amount": ((sales_adjustment_amount == 0) ? '' : credit_note_symbol) + (sales_adjustment_amount ? new Intl.NumberFormat(currency_locale, {
        style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(sales_adjustment_amount) : new Intl.NumberFormat(currency_locale, {
        style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0)),
                          "vat_check_25": vat_check_25,
                          "currency": sales_currency,
                          "credit_note": parseInt(invoice['credit_note']),
                          "doc_status": invoice['doc_status'],    
                          "comment_reason": invoice['comment_reason'],
                          "comment": invoice['comment'],
                          "comment_visiblity": invoice['comment_visiblity'],

                          "convert_currency_code": invoice['convert_currency_code'],
                          "convert_net_amount": (invoice['convert_net_amount']) ? new Intl.NumberFormat(currency_locale, {
        style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(invoice['convert_net_amount']) : null,
                          "convert_vat_amount": (invoice['convert_vat_amount']) ? new Intl.NumberFormat(currency_locale, {
        style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(invoice['convert_vat_amount']) : null,
                          "convert_total_amount": (invoice['convert_total_amount']) ? new Intl.NumberFormat(currency_locale, {
        style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(invoice['convert_total_amount']) : null,
                          "exchange_rate": invoice['exchange_rate'],

                          "disregard_invoice": invoice['disregard_invoice'],                          
                          "disregard_reason": invoice['disregard_reason'],  
                          "disregard_comment": invoice['disregard_comment'],  
                          "disregard_comment_visiblity": invoice['disregard_comment_visiblity']
                        } 
                      );

                      if(invoice['currency_code'] == 'NOK')
                      {     
                        if(sales_vat_amount)  
                        {         
                          if(invoice_date in total_vat_amount)            
                            total_vat_amount[invoice_date] += parseFloat(sales_vat_amount);
                          else
                            total_vat_amount[invoice_date] = parseFloat(sales_vat_amount);
                        }

                        if(sales_net_amount)  
                        {
                          if(invoice_date in total_net_amount) 
                            total_net_amount[invoice_date] += parseFloat(sales_net_amount);
                          else
                            total_net_amount[invoice_date] = parseFloat(sales_net_amount);
                        }
                      }
                    //}//NOT disregard sales invoices
                  }//com_invoice_id match
                }); //Sales Invoices
                        
                if(already_lope_no_exist)
                {
                  if(index >= 0)
                  {
                    var prev_sub_invoices = sub_cominvoices[com_invoice_date][index]['invoices'];                      
                    sub_cominvoices[com_invoice_date][index]['invoices'] = prev_sub_invoices.concat(sub_invoices);
                  }
                }
                else
                  sub_cominvoices[com_invoice_date][index-1]['invoices'] = sub_invoices;              
            }//remove already matched com.invoices 
          } //not MA and SO
        });  //Com. Invoices

        /*Other Periods Com. Invoices*/        
        var modal_cominvoices_other_periods = [];

        $.each(other_period_importreconciliationcominvoices, function (idx, other_period) {   
          if(other_period['invoice_no'] != '')      
          {
            modal_cominvoices_other_periods.push(
              {
                "id": other_period['id'],                    
                "co_invoice_no": other_period['invoice_no'],
                "other_period": true
              } 
            );
          }
        });

        if(Object.keys(modal_cominvoices_other_periods).length > 0)        
          modal_cominvoices_other_periods.sort((a, b) => a.co_invoice_no.localeCompare(b.co_invoice_no));              
        /*Other Periods Com. Invoices*/

        /*Credit Notes / No Sales References in GS but has SFTP Files*/
        var filter_credit_note_files = importreconciliationfiles.filter(function(obj) {                                        
            return (obj.credit_note) || 
              (!importreconciliationsalesinvoices.some(function(e) {
                return e.invoice_no === obj.invoice_no;
              })
            );
        }); 
        
        if(filter_credit_note_files.length > 0)
        {      
          filter_credit_note_files.sort((a, b) => a.invoice_no.localeCompare(b.invoice_no));

          var credit_note_symbol = '';
          var sub_invoices = [];    
          $.each(filter_credit_note_files, function (idx, credit_note) {            
            var invoice = credit_note['salesinvoicesdata'];
            var sales_xml_id = credit_note['id'];
            var edit_from = 'xml';//invoice['id'];

            if(invoice)
            {
              var sales_net_amount = invoice['tax_total_net_amount'];
              var sales_shipping = 0;
              var sales_variance = 0;
              var sales_vat_amount = invoice['tax_total_amount'];
              var sales_currency = invoice['tax_total_amount_currency_code'];

              /*Convert EUR to NOK*/              
              //Extract converted amount from note
              var tax_total_net_amount = 0;
              var tax_total_amount = 0;
              var tax_total_amount_currency_code = '';
              if(invoice['currency_code'] != 'NOK')
              {
                var footer_note = $.trim(invoice['converted_note']);                              
                if(footer_note.indexOf(' alt ') != -1)  
                { 
                  let arr_footer_note = footer_note.split(" ");

                  // Find indexes where value is 'NOK'
                  let indexes = arr_footer_note
                      .map((val, i) => val === 'NOK' ? i : -1)
                      .filter(i => i !== -1);

                  // Get and normalize the next values
                  let amountsAfterNOK = indexes.map(i => {
                      let val = arr_footer_note[i + 1];
                      if (val === undefined) return null;

                      return val
                          .toString()
                          .replace(/\n.*/, '')
                          .replace(/\./g, '')
                          .replace(/'/g, '')
                          .replace(/,/g, '.');
                  });

                  tax_total_net_amount = amountsAfterNOK[0];
                  tax_total_amount = amountsAfterNOK[1];
                  tax_total_amount_currency_code = 'NOK';
                }
              }//if not NOK
              //Extract converted amount from note

              if(!invoice['note'])              
              {
                if(invoice['currency_code'] != 'NOK')
                {
                  sales_net_amount = tax_total_net_amount;                          
                  sales_vat_amount = tax_total_amount;
                  sales_currency = tax_total_amount_currency_code;
                }                
              }              
              /*Convert EUR to NOK*/

              var vat_check_25 = 0;
              if(sales_net_amount > 0)
              {  
                var invoice_shipping = (sales_shipping) ? parseFloat(sales_shipping) : 0;
                var invoice_variance = (sales_variance) ? parseFloat(sales_variance) : 0;
                var invoice_net_amount = (sales_net_amount) ? parseFloat(sales_net_amount) : 0;
                var invoice_vat_amount = (sales_vat_amount) ? parseFloat(sales_vat_amount) : 0;

                var invoice_vat_percent = (invoice_vat_amount/(invoice_net_amount + invoice_shipping + invoice_variance)) * 100;

                var minimum_fraction_digits = (vatregmain['country'] == 'CH') ? 1 : 0;
               
                vat_check_25 = new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: minimum_fraction_digits, maximumFractionDigits: minimum_fraction_digits}).format(invoice_vat_percent);      
              }
                
              sub_invoices.push(
                {
                  "id": invoice['id'],//salesinvoicedata ID
                  "pdf": sales_xml_id,
                  "edit_from": edit_from,                          
                  "invoice_no": invoice['invoice_no'],
                  "invoice_date": moment(invoice['invoice_date']).format('DD-MM-YYYY'),
                  "o_invoice_date": invoice['invoice_date'],
                  "is_net_amount_null": (sales_net_amount) ? false : true,
                  "net_amount": ((sales_net_amount == 0) ? '' : credit_note_symbol) + (sales_net_amount ? new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(sales_net_amount) : new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0)),
                  "shipping": ((sales_shipping == 0) ? '' : credit_note_symbol) + (sales_shipping ? new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(sales_shipping) : new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0)),
                  "variance": ((sales_variance == 0) ? '' : credit_note_symbol) + (sales_variance ? new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(sales_variance) : new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0)),
                  "vat_amount": ((sales_vat_amount == 0) ? '' : credit_note_symbol) + (sales_vat_amount ? new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(sales_vat_amount) : new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0)),
                  "adjustment_amount": new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0),
                  "vat_check_25": vat_check_25,
                  "currency": sales_currency,
                  "credit_note": parseInt(invoice['credit_note']),
                  "doc_status": null,    
                  "comment_reason": null,
                  "comment": null,
                  "comment_visiblity": null,

                  "convert_currency_code": null,
                  "convert_net_amount": (invoice['convert_net_amount']) ? new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(invoice['convert_net_amount']) : null,
                  "convert_vat_amount": (invoice['convert_vat_amount']) ? new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(invoice['convert_vat_amount']) : null,
                  "convert_total_amount": (invoice['convert_total_amount']) ? new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(invoice['convert_total_amount']) : null,
                  "exchange_rate": invoice['exchange_rate'],

                  "disregard_invoice": null,                          
                  "disregard_reason": null,  
                  "disregard_comment": null,  
                  "disregard_comment_visiblity": null
                } 
              );
            }//invoice
          });

          if(sub_invoices)
          {
            filter_credit_note_files.sort((a, b) => a.month_year.localeCompare(b.month_year));

            if(filter_credit_note_files[0].month_year in sub_cominvoices)   
            {}
            else
              sub_cominvoices[filter_credit_note_files[0].month_year] = [];

            var index = sub_cominvoices[filter_credit_note_files[0].month_year].push(
              {
                "id": '-',
                "country": vatregmain['country'],
                "pdf": '-',
                "co_invoice_no": '-',     
                "orginal_co_invoice_no": '-',   
                "expo_no": '-',   
                "lope_no": '-',     
                "duties": new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0),
                "vat_on_duties": new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0),
                "adjustment": new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0),
                "vat_on_adjustment": new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0),
                "statistical_value": new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0),
                "category_type": null,
                "category_desc": 'Credit Notes/Missing Ref.',//'Credit Notes',
                "o_invoice_date": '-',   
                "ivf_net_amount": new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0),
                "com_net_amount": new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0),
                "net_amount_co_invoice": new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0),
                "import_vat": new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0),
                "currency": null,
                "doc_status": '-',
                "unmatch": null,
                "rematch_com_invoice_id": null,
                "doc_id": null,

                "convert_currency_code": null,
                "convert_net_amount": null,
                "convert_vat_amount": null,
                "convert_total_amount": null,
                "exchange_rate": '-',

                "disregard_invoice": null,
                "disregard_type": null,
                "disregarded_no": null,
                "disregard_reason": null,
                "disregard_comment": null,
                "disregard_comment_visiblity": null,

                "comment_reason": null,
                "comment": null,
                "comment_visiblity": null,
                "no_of_split": null,
                "group_lope_no": null,

                "invoices": sub_invoices,
              } 
            );
          } //sub_invoices
        }
        /*Credit Notes / No Sales References in GS but has SFTP Files*/

        var dt_declarations_tables = $('.datatables-declarations');       
        var declaration_datas = [];
        //var control_class = '';
        var control_html = '';
        let compare_month_year = '';
        for (var i = 0; i < dt_declarations_tables.length; i++) 
        {         
          compare_month_year = moment(declarations['service_start']).add(i, 'month').format('MM-Y');

          if(i === 0)
          {            
            //control_class = 'first';
            declaration_first_datas = [];
            declaration_datas = declaration_first_datas;            
          }
          else if(i === 1)
          {
            //control_class = 'second';
            declaration_second_datas = [];
            declaration_datas = declaration_second_datas;                        
          }
          else if(i === 2)
          {
            //control_class = 'third';
            declaration_third_datas = [];
            declaration_datas = declaration_third_datas;            
          }

          var ivf_exist = false;
          var ivf_index = 0;
          if(vatregmain['country'] == 'NO')
          {
            //Dummy First LINE when no IVF
            $.each(importvatfiles, function (idx, importvatfile) {
              if(importvatfile['file_type'] == 'xml')
              {
                if(importvatfile['month_year'] == compare_month_year)  
                {          
                  ivf_exist = true;
                  ivf_index = idx;
                }
              } //XML
            });//loop IVF

            if(!ivf_exist)
            {
              var net_amount_commercial_invoice = new Intl.NumberFormat(currency_locale, {
          style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(parseFloat(total_com_invoice_net_amount[compare_month_year]));
                var net_amount_sales_invoice = new Intl.NumberFormat(currency_locale, {
          style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(parseFloat(total_net_amount[compare_month_year]));
                var vat_amount_sales_invoice = new Intl.NumberFormat(currency_locale, {
          style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(parseFloat(total_vat_amount[compare_month_year]));
                var sales_vat_vs_import_vat = new Intl.NumberFormat(currency_locale, {
          style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format((parseFloat(total_vat_amount[compare_month_year]) - (parseFloat(0) * vat_percent)));

                var control_li_html = '<li class="d-flex mb-4 pb-1">' +
                                          '<div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">' +
                                            '<div class="me-2">' +
                                              '<h6 class="mb-0">Net Amount Commercial Invoice</h6>' +
                                            '</div>' +
                                            '<div class="user-progress">' +
                                              '<small class="fw-medium">' + net_amount_commercial_invoice +'</small><i class="bx '+ ((net_amount_commercial_invoice >= 0) ? 'bx-chevron-up text-success' : 'bx-chevron-down text-danger') +' ms-1"></i>' +
                                            '</div>' +
                                          '</div>' +
                                        '</li>' +

                                        '<li class="d-flex mb-4 pb-1">' +
                                          '<div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">' +
                                            '<div class="me-2">' +
                                              '<h6 class="mb-0">Net Amount Sales Invoice</h6>' +
                                            '</div>' +
                                            '<div class="user-progress">' +
                                              '<small class="fw-medium">'+ net_amount_sales_invoice +'</small><i class="bx '+ ((net_amount_sales_invoice >= 0) ? 'bx-chevron-up text-success' : 'bx-chevron-down text-danger') +' ms-1"></i>' +
                                            '</div>' +
                                          '</div>' +
                                        '</li>' +

                                        '<li class="d-flex mb-4 pb-1">' +
                                          '<div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">' +
                                            '<div class="me-2">' +
                                              '<h6 class="mb-0">VAT Amount Sales Invoice</h6>' +
                                            '</div>' +
                                            '<div class="user-progress">' +
                                              '<small class="fw-medium">'+ vat_amount_sales_invoice +'</small><i class="bx '+ ((vat_amount_sales_invoice >= 0) ? 'bx-chevron-up text-success' : 'bx-chevron-down text-danger') +' ms-1"></i>' +
                                            '</div>' +
                                          '</div>' +
                                        '</li>' +

                                        '<li class="d-flex mb-4 pb-1">' +
                                          '<div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">' +
                                            '<div class="me-2">' +
                                              '<h6 class="mb-0">Sales VAT vs Import VAT</h6>' +
                                            '</div>' +
                                            '<div class="user-progress">' +
                                              '<small class="fw-medium">'+ sales_vat_vs_import_vat +'</small><i class="bx '+ ((sales_vat_vs_import_vat >= 0) ? 'bx-chevron-up text-success' : 'bx-chevron-down text-danger') +' ms-1"></i>' +
                                            '</div>' +
                                          '</div>' +
                                        '</li>';

              let co_invoices = [];
              if(Object.keys(sub_cominvoices).length > 0)
              {
                //if($.inArray(compare_month_year, sub_cominvoices) == -1)
                if (sub_cominvoices.hasOwnProperty(compare_month_year))
                  co_invoices = sub_cominvoices[compare_month_year];
              }

              let modal_co_invoices = [];
              if(Object.keys(modal_cominvoices).length == 0)              
                modal_co_invoices.push(...modal_cominvoices_other_periods);              
              else
              {          
                if (modal_cominvoices.hasOwnProperty(compare_month_year))
                {
                  modal_co_invoices = modal_cominvoices[compare_month_year];
                  modal_co_invoices.sort((a, b) => a.co_invoice_no.localeCompare(b.co_invoice_no));

                  let filter_other_period_com_invoices = modal_cominvoices_other_periods.filter(item => 
                    !modal_cominvoices[compare_month_year].some(startItem => startItem.id === item.id)
                  );

                  modal_co_invoices.push(...filter_other_period_com_invoices);
                }
              }                                                        

              if(co_invoices)
                //co_invoices.sort((a, b) => a.co_invoice_no.localeCompare(b.co_invoice_no)); 
                co_invoices.sort((a, b) => {
                  if (a.id === '-') return 1;
                  if (b.id === '-') return -1;
                  return a.co_invoice_no.localeCompare(b.co_invoice_no);
                });
             
              declaration_datas.push({                 
                'id' : 0,   
                'fake_id' : 0, 
                'country': vatregmain['country'],
                'month_year': compare_month_year,
                'pdf' : "PDF", 
                'declaration_no'  : org_no,                            
                'o_declaration_date' : '01-' + compare_month_year, 
                'duties' : new Intl.NumberFormat(currency_locale, {
      style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0),     
                'net_amount' : new Intl.NumberFormat(currency_locale, {
      style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0), 
                'adjustment' : new Intl.NumberFormat(currency_locale, {
      style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0),
                'statistical_value' : new Intl.NumberFormat(currency_locale, {
      style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0), 
                'import_vat' : new Intl.NumberFormat(currency_locale, {
      style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0),//((duties + statistical_value) * 0.25)
                'vat_on_duties' : new Intl.NumberFormat(currency_locale, {
      style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0),//(duties * 0.25)
                'vat_on_adjustment' : new Intl.NumberFormat(currency_locale, {
      style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0),//(adjustment * 0.25)
                'net_amount_commercial_invoice' : new Intl.NumberFormat(currency_locale, {
      style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0), 
                'net_amount_sales_invoice' : new Intl.NumberFormat(currency_locale, {
      style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0), 
                'vat_amount_sales_invoice' : new Intl.NumberFormat(currency_locale, {
      style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0), 
                'sales_vat_vs_import_vat' : new Intl.NumberFormat(currency_locale, {
      style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0), //(vat_amount_sales_invoice - (net_amount * 0.25)) 
                'currency': currency_style,
                'comment_reason': '',
                'comment': '',
                'comment_visiblity': '',
               
                'import_vat_xml': [],
                'co_invoices': co_invoices,
                'modal_co_invoices': modal_co_invoices
              });

              var control_class = '';             
              if(i === 0)          
                control_class = 'first'; 
              else if(i === 1)
                control_class = 'second';
              else if(i === 2)
                control_class = 'third';            

              if(control_class != '')
                control_html += '<ul class="p-0 m-0 '+ control_class +'">' +
                                    control_li_html +
                                  '</ul>';   
            } //No IVF for TAB
            //Dummy First LINE when no IVF


            //var control_html = '';
            var declaration_start = 1;
            $.each(importvatfiles, function (idx, importvatfile) {
              if(importvatfile['file_type'] == 'xml' && (importvatfile['month_year'] == compare_month_year))
              {               
                var statistical_value = new Intl.NumberFormat(currency_locale, {
          style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(importvatfile['statistical_number']);
                var net_amount = new Intl.NumberFormat(currency_locale, {
          style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(importvatfile['invoice_total']);

                var net_amount_commercial_invoice = new Intl.NumberFormat(currency_locale, {
          style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(parseFloat(total_com_invoice_net_amount[importvatfile['month_year']]));
                var net_amount_sales_invoice = new Intl.NumberFormat(currency_locale, {
          style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(parseFloat(total_net_amount[importvatfile['month_year']]));
                var vat_amount_sales_invoice = new Intl.NumberFormat(currency_locale, {
          style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(parseFloat(total_vat_amount[importvatfile['month_year']]));
                var sales_vat_vs_import_vat = new Intl.NumberFormat(currency_locale, {
          style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format((parseFloat(total_vat_amount[importvatfile['month_year']]) - (parseFloat(importvatfile['invoice_total']) * vat_percent)));

                var control_li_html = '<li class="d-flex mb-4 pb-1">' +
                                          '<div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">' +
                                            '<div class="me-2">' +
                                              '<h6 class="mb-0">Net Amount Commercial Invoice</h6>' +
                                            '</div>' +
                                            '<div class="user-progress">' +
                                              '<small class="fw-medium">' + net_amount_commercial_invoice +'</small><i class="bx '+ ((net_amount_commercial_invoice >= 0) ? 'bx-chevron-up text-success' : 'bx-chevron-down text-danger') +' ms-1"></i>' +
                                            '</div>' +
                                          '</div>' +
                                        '</li>' +

                                        '<li class="d-flex mb-4 pb-1">' +
                                          '<div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">' +
                                            '<div class="me-2">' +
                                              '<h6 class="mb-0">Net Amount Sales Invoice</h6>' +
                                            '</div>' +
                                            '<div class="user-progress">' +
                                              '<small class="fw-medium">'+ net_amount_sales_invoice +'</small><i class="bx '+ ((net_amount_sales_invoice >= 0) ? 'bx-chevron-up text-success' : 'bx-chevron-down text-danger') +' ms-1"></i>' +
                                            '</div>' +
                                          '</div>' +
                                        '</li>' +

                                        '<li class="d-flex mb-4 pb-1">' +
                                          '<div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">' +
                                            '<div class="me-2">' +
                                              '<h6 class="mb-0">VAT Amount Sales Invoice</h6>' +
                                            '</div>' +
                                            '<div class="user-progress">' +
                                              '<small class="fw-medium">'+ vat_amount_sales_invoice +'</small><i class="bx '+ ((vat_amount_sales_invoice >= 0) ? 'bx-chevron-up text-success' : 'bx-chevron-down text-danger') +' ms-1"></i>' +
                                            '</div>' +
                                          '</div>' +
                                        '</li>' +

                                        '<li class="d-flex mb-4 pb-1">' +
                                          '<div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">' +
                                            '<div class="me-2">' +
                                              '<h6 class="mb-0">Sales VAT vs Import VAT</h6>' +
                                            '</div>' +
                                            '<div class="user-progress">' +
                                              '<small class="fw-medium">'+ sales_vat_vs_import_vat +'</small><i class="bx '+ ((sales_vat_vs_import_vat >= 0) ? 'bx-chevron-up text-success' : 'bx-chevron-down text-danger') +' ms-1"></i>' +
                                            '</div>' +
                                          '</div>' +
                                        '</li>';

                var control_class = '';
                if(ivf_exist && (importvatfile['month_year'] == compare_month_year))                       
                {   
                  let co_invoices = [];
                  if(Object.keys(sub_cominvoices).length > 0)
                  {
                    //if($.inArray(importvatfile['month_year'], sub_cominvoices) == -1)
                    if (sub_cominvoices.hasOwnProperty(importvatfile['month_year']))
                      co_invoices = sub_cominvoices[importvatfile['month_year']];
                  }

                  let modal_co_invoices = [];
                  if(Object.keys(modal_cominvoices).length == 0)
                    modal_co_invoices.push(...modal_cominvoices_other_periods);              
                  else
                  {               
                    if (modal_cominvoices.hasOwnProperty(importvatfile['month_year']))
                    {
                      modal_co_invoices = modal_cominvoices[importvatfile['month_year']];
                      modal_co_invoices.sort((a, b) => a.co_invoice_no.localeCompare(b.co_invoice_no));

                      let filter_other_period_com_invoices_first = modal_cominvoices_other_periods.filter(item => 
                        !modal_cominvoices[importvatfile['month_year']].some(startItem => startItem.id === item.id)
                      );
            
                      modal_co_invoices.push(...filter_other_period_com_invoices_first);
                    }
                  }                          
     
                  if(co_invoices)
                    //co_invoices.sort((a, b) => a.co_invoice_no.localeCompare(b.co_invoice_no)); 
                    co_invoices.sort((a, b) => {
                      if (a.id === '-') return 1;
                      if (b.id === '-') return -1;
                      return a.co_invoice_no.localeCompare(b.co_invoice_no);
                    });

                  declaration_datas.push({                 
                    'id' : importvatfile['id'],   
                    'fake_id' : declaration_start, 
                    'country': vatregmain['country'],
                    'month_year': compare_month_year,
                    'pdf' : "PDF", 
                    'declaration_no'  : org_no,                            
                    'o_declaration_date' : '01-' + importvatfile['month_year'], 
                    'duties' : new Intl.NumberFormat(currency_locale, {
          style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(importvatfile['fee_number']),     
                    'net_amount' : net_amount,
                    'adjustment' : new Intl.NumberFormat(currency_locale, {
          style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(importvatfile['adjustment_no']),
                    'statistical_value' : statistical_value,
                    'import_vat' : new Intl.NumberFormat(currency_locale, {
          style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(((parseFloat(importvatfile['fee_number']) + parseFloat(importvatfile['statistical_number'])) * vat_percent)),//((duties + statistical_value) * 0.25)
                    'vat_on_duties' : new Intl.NumberFormat(currency_locale, {
          style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format((parseFloat(importvatfile['fee_number']) * vat_percent)),//(duties * 0.25)
                    'vat_on_adjustment' : new Intl.NumberFormat(currency_locale, {
          style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format((parseFloat(importvatfile['adjustment_no']) * vat_percent)),//(adjustment * 0.25)
                    'net_amount_commercial_invoice' : net_amount_commercial_invoice,
                    'net_amount_sales_invoice' : net_amount_sales_invoice,
                    'vat_amount_sales_invoice' : vat_amount_sales_invoice,
                    'sales_vat_vs_import_vat' : sales_vat_vs_import_vat,//(vat_amount_sales_invoice - (net_amount * 0.25)) 
                    'currency': currency_style,
                    'comment_reason': importvatfile['comment_reason'],
                    'comment': importvatfile['comment'],
                    'comment_visiblity': importvatfile['comment_visiblity'],
                   
                    'import_vat_xml': importvatfile['xml'],
                    'co_invoices': co_invoices,
                    'modal_co_invoices': modal_co_invoices
                  });               

                  //control_class = 'first';
                  if(ivf_index === 0)          
                    control_class = 'first'; 
                  else if(ivf_index === 1)
                    control_class = 'second';
                  else if(ivf_index === 2)
                    control_class = 'third';
                 
                  declaration_start = declaration_start + 1;            
                } //has IVF for TAB 

                if(control_class != '')
                  control_html += '<ul class="p-0 m-0 '+ control_class +'">' +
                                      control_li_html +
                                    '</ul>';                               
              } //only XML
            });  

            $(".form-declaration-control .declaration-control").html(control_html);
          }//NO
          else //if(vatregmain['country'] == 'CH')          
          {
            let co_invoices = [];
            if(Object.keys(sub_cominvoices).length > 0)
            {
              //if($.inArray(compare_month_year, sub_cominvoices) == -1)
              if (sub_cominvoices.hasOwnProperty(compare_month_year))
                co_invoices = sub_cominvoices[compare_month_year];
            }

            let modal_co_invoices = [];
            if(Object.keys(modal_cominvoices).length == 0)
              modal_co_invoices.push(...modal_cominvoices_other_periods);              
            else
            {          
              if (modal_cominvoices.hasOwnProperty(compare_month_year))
              {
                modal_co_invoices = modal_cominvoices[compare_month_year];
                modal_co_invoices.sort((a, b) => a.co_invoice_no.localeCompare(b.co_invoice_no));

                let filter_other_period_com_invoices = modal_cominvoices_other_periods.filter(item => 
                  !modal_cominvoices[compare_month_year].some(startItem => startItem.id === item.id)
                );

                modal_co_invoices.push(...filter_other_period_com_invoices);
              }
            }                                                        
          
            if(co_invoices)
              co_invoices.sort((a, b) => a.co_invoice_no.localeCompare(b.co_invoice_no)); 

            declaration_datas.push({                 
              'id' : 0,   
              'fake_id' : 0, 
              'country': vatregmain['country'],
              'month_year': compare_month_year,
              'pdf' : "PDF", 
              'declaration_no'  : org_no,                            
              'o_declaration_date' : '01-' + compare_month_year, 
              'duties' : new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0),     
              'net_amount' : new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0), 
              'adjustment' : new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0),
              'statistical_value' : new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0), 
              'import_vat' : new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0),//((duties + statistical_value) * 0.25)
              'vat_on_duties' : new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0),//(duties * 0.25)
              'vat_on_adjustment' : new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0),//(adjustment * 0.25)
              'net_amount_commercial_invoice' : new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0), 
              'net_amount_sales_invoice' : new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0), 
              'vat_amount_sales_invoice' : new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0), 
              'sales_vat_vs_import_vat' : new Intl.NumberFormat(currency_locale, {
    style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0), //(vat_amount_sales_invoice - (net_amount * 0.25)) 
              'currency': currency_style,
              'comment_reason': '',
              'comment': '',
              'comment_visiblity': '',
             
              'import_vat_xml': [],
              'co_invoices': co_invoices,
              'modal_co_invoices': modal_co_invoices
            });
          }

          if(i === 0)          
            declaration_first_datas = declaration_datas;            
          else if(i === 1)
            declaration_second_datas = declaration_datas;            
          else if(i === 2)
            declaration_third_datas = declaration_datas;                
        } //for loop tabs        

        return {
          'declaration_first_datas' : declaration_first_datas, 
          'declaration_second_datas' : declaration_second_datas,
          'declaration_third_datas' : declaration_third_datas
        };  
/*
        var ivf_first = false;
        var ivf_second = false;       
        //Dummy First LINE when no IVF          
        $.each(importvatfiles, function (idx, importvatfile) {
          if(importvatfile['file_type'] == 'xml')
          {
            if(importvatfile['month_year'] == start_month_year)            
              ivf_first = true;

            if(importvatfile['month_year'] == end_month_year)
              ivf_second = true;
          } //XML
        });

        if(!ivf_first)
        {
          let co_invoices = [];
          if(Object.keys(sub_cominvoices).length > 0)
          {
            if($.inArray(start_month_year, sub_cominvoices) == -1)
              co_invoices = sub_cominvoices[start_month_year];
          }

          let modal_co_invoices = [];
          if(Object.keys(modal_cominvoices).length > 0)
          {          
            if (modal_cominvoices.hasOwnProperty(start_month_year))
            {
              modal_co_invoices = modal_cominvoices[start_month_year];
              modal_co_invoices.sort((a, b) => a.co_invoice_no.localeCompare(b.co_invoice_no));

              let filter_other_period_com_invoices_first = modal_cominvoices_other_periods.filter(item => 
                !modal_cominvoices[start_month_year].some(startItem => startItem.id === item.id)
              );

              modal_co_invoices.push(...filter_other_period_com_invoices_first);
            }
          }                                                        

          declaration_first_datas.push({                 
            'id' : 0,   
            'fake_id' : 0, 
            'pdf' : "PDF", 
            'declaration_no'  : org_no,                            
            'o_declaration_date' : '01-' + start_month_year, 
            'duties' : new Intl.NumberFormat(currency_locale, {
  style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0),     
            'net_amount' : new Intl.NumberFormat(currency_locale, {
  style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0), 
            'adjustment' : new Intl.NumberFormat(currency_locale, {
  style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0),
            'statistical_value' : new Intl.NumberFormat(currency_locale, {
  style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0), 
            'import_vat' : new Intl.NumberFormat(currency_locale, {
  style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0),//((duties + statistical_value) * 0.25)
            'vat_on_duties' : new Intl.NumberFormat(currency_locale, {
  style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0),//(duties * 0.25)
            'vat_on_adjustment' : new Intl.NumberFormat(currency_locale, {
  style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0),//(adjustment * 0.25)
            'net_amount_commercial_invoice' : new Intl.NumberFormat(currency_locale, {
  style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0), 
            'net_amount_sales_invoice' : new Intl.NumberFormat(currency_locale, {
  style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0), 
            'vat_amount_sales_invoice' : new Intl.NumberFormat(currency_locale, {
  style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0), 
            'sales_vat_vs_import_vat' : new Intl.NumberFormat(currency_locale, {
  style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0), //(vat_amount_sales_invoice - (net_amount * 0.25)) 
            'currency': currency_style,
            'comment_reason': '',
            'comment': '',
            'comment_visiblity': '',
           
            'import_vat_xml': [],
            'co_invoices': co_invoices,
            'modal_co_invoices': modal_co_invoices
          });
        } //No IVF for FIRST TAB

        if(!ivf_second)
        {
          let co_invoices = [];
          if(Object.keys(sub_cominvoices).length > 0)
          {
            if($.inArray(end_month_year, sub_cominvoices) == -1)
              co_invoices = sub_cominvoices[end_month_year];
          }

          let modal_co_invoices = [];
          if(Object.keys(modal_cominvoices).length > 0)
          {            
            if (modal_cominvoices.hasOwnProperty(end_month_year))
            {
              modal_co_invoices = modal_cominvoices[end_month_year];
              modal_co_invoices.sort((a, b) => a.co_invoice_no.localeCompare(b.co_invoice_no));

              let filter_other_period_com_invoices_second = modal_cominvoices_other_periods.filter(item => 
                !modal_cominvoices[end_month_year].some(startItem => startItem.id === item.id)
              );

              modal_co_invoices.push(...filter_other_period_com_invoices_second);
            }                
          }          

          declaration_second_datas = [];
          declaration_second_datas.push({                 
            'id' : 0,   
            'fake_id' : 0, 
            'pdf' : "PDF", 
            'declaration_no'  : org_no,                            
            'o_declaration_date' : '01-' + start_month_year, 
            'duties' : new Intl.NumberFormat(currency_locale, {
  style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0),     
            'net_amount' : new Intl.NumberFormat(currency_locale, {
  style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0), 
            'adjustment' : new Intl.NumberFormat(currency_locale, {
  style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0),
            'statistical_value' : new Intl.NumberFormat(currency_locale, {
  style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0), 
            'import_vat' : new Intl.NumberFormat(currency_locale, {
  style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0),//((duties + statistical_value) * 0.25)
            'vat_on_duties' : new Intl.NumberFormat(currency_locale, {
  style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0),//(duties * 0.25)
            'vat_on_adjustment' : new Intl.NumberFormat(currency_locale, {
  style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0),//(adjustment * 0.25)
            'net_amount_commercial_invoice' : new Intl.NumberFormat(currency_locale, {
  style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0), 
            'net_amount_sales_invoice' : new Intl.NumberFormat(currency_locale, {
  style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0), 
            'vat_amount_sales_invoice' : new Intl.NumberFormat(currency_locale, {
  style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0), 
            'sales_vat_vs_import_vat' : new Intl.NumberFormat(currency_locale, {
  style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(0), //(vat_amount_sales_invoice - (net_amount * 0.25)) 
            'currency': currency_style,
            'comment_reason': '',
            'comment': '',
            'comment_visiblity': '',
           
            'import_vat_xml': [],
            'co_invoices': co_invoices,
            'modal_co_invoices': modal_co_invoices
          });            
        } //No IVF for SECOND TAB
        //Dummy First LINE when no IVF

        var control_html = '';
        $.each(importvatfiles, function (idx, importvatfile) {
          if(importvatfile['file_type'] == 'xml')
          {               
            var statistical_value = new Intl.NumberFormat(currency_locale, {
      style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(importvatfile['statistical_number']);
            var net_amount = new Intl.NumberFormat(currency_locale, {
      style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(importvatfile['invoice_total']);

            var net_amount_commercial_invoice = new Intl.NumberFormat(currency_locale, {
      style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(parseFloat(total_com_invoice_net_amount[importvatfile['month_year']]));
            var net_amount_sales_invoice = new Intl.NumberFormat(currency_locale, {
      style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(parseFloat(total_net_amount[importvatfile['month_year']]));
            var vat_amount_sales_invoice = new Intl.NumberFormat(currency_locale, {
      style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(parseFloat(total_vat_amount[importvatfile['month_year']]));
            var sales_vat_vs_import_vat = new Intl.NumberFormat(currency_locale, {
      style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format((parseFloat(total_vat_amount[importvatfile['month_year']]) - (parseFloat(importvatfile['invoice_total']) * vat_percent)));

            var control_li_html = '<li class="d-flex mb-4 pb-1">' +
                                      '<div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">' +
                                        '<div class="me-2">' +
                                          '<h6 class="mb-0">Net Amount Commercial Invoice</h6>' +
                                        '</div>' +
                                        '<div class="user-progress">' +
                                          '<small class="fw-medium">' + net_amount_commercial_invoice +'</small><i class="bx '+ ((net_amount_commercial_invoice >= 0) ? 'bx-chevron-up text-success' : 'bx-chevron-down text-danger') +' ms-1"></i>' +
                                        '</div>' +
                                      '</div>' +
                                    '</li>' +

                                    '<li class="d-flex mb-4 pb-1">' +
                                      '<div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">' +
                                        '<div class="me-2">' +
                                          '<h6 class="mb-0">Net Amount Sales Invoice</h6>' +
                                        '</div>' +
                                        '<div class="user-progress">' +
                                          '<small class="fw-medium">'+ net_amount_sales_invoice +'</small><i class="bx '+ ((net_amount_sales_invoice >= 0) ? 'bx-chevron-up text-success' : 'bx-chevron-down text-danger') +' ms-1"></i>' +
                                        '</div>' +
                                      '</div>' +
                                    '</li>' +

                                    '<li class="d-flex mb-4 pb-1">' +
                                      '<div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">' +
                                        '<div class="me-2">' +
                                          '<h6 class="mb-0">VAT Amount Sales Invoice</h6>' +
                                        '</div>' +
                                        '<div class="user-progress">' +
                                          '<small class="fw-medium">'+ vat_amount_sales_invoice +'</small><i class="bx '+ ((vat_amount_sales_invoice >= 0) ? 'bx-chevron-up text-success' : 'bx-chevron-down text-danger') +' ms-1"></i>' +
                                        '</div>' +
                                      '</div>' +
                                    '</li>' +

                                    '<li class="d-flex mb-4 pb-1">' +
                                      '<div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">' +
                                        '<div class="me-2">' +
                                          '<h6 class="mb-0">Sales VAT vs Import VAT</h6>' +
                                        '</div>' +
                                        '<div class="user-progress">' +
                                          '<small class="fw-medium">'+ sales_vat_vs_import_vat +'</small><i class="bx '+ ((sales_vat_vs_import_vat >= 0) ? 'bx-chevron-up text-success' : 'bx-chevron-down text-danger') +' ms-1"></i>' +
                                        '</div>' +
                                      '</div>' +
                                    '</li>';

            var control_class = '';

            if(ivf_first && (importvatfile['month_year'] == start_month_year))                       
            {   
              let co_invoices = [];
              if(Object.keys(sub_cominvoices).length > 0)
              {
                if($.inArray(importvatfile['month_year'], sub_cominvoices) == -1)
                  co_invoices = sub_cominvoices[importvatfile['month_year']];
              }

              let modal_co_invoices = [];
              if(Object.keys(modal_cominvoices).length > 0)
              {               
                if (modal_cominvoices.hasOwnProperty(importvatfile['month_year']))
                {
                  modal_co_invoices = modal_cominvoices[importvatfile['month_year']];
                  modal_co_invoices.sort((a, b) => a.co_invoice_no.localeCompare(b.co_invoice_no));

                  let filter_other_period_com_invoices_first = modal_cominvoices_other_periods.filter(item => 
                    !modal_cominvoices[importvatfile['month_year']].some(startItem => startItem.id === item.id)
                  );
        
                  modal_co_invoices.push(...filter_other_period_com_invoices_first);
                }
              }                          
 
              declaration_first_datas.push({                 
                'id' : importvatfile['id'],   
                'fake_id' : declaration_first_start, 
                'pdf' : "PDF", 
                'declaration_no'  : org_no,                            
                'o_declaration_date' : '01-' + importvatfile['month_year'], 
                'duties' : new Intl.NumberFormat(currency_locale, {
      style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(importvatfile['fee_number']),     
                'net_amount' : net_amount,
                'adjustment' : new Intl.NumberFormat(currency_locale, {
      style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(importvatfile['adjustment_no']),
                'statistical_value' : statistical_value,
                'import_vat' : new Intl.NumberFormat(currency_locale, {
      style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(((parseFloat(importvatfile['fee_number']) + parseFloat(importvatfile['statistical_number'])) * vat_percent)),//((duties + statistical_value) * 0.25)
                'vat_on_duties' : new Intl.NumberFormat(currency_locale, {
      style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format((parseFloat(importvatfile['fee_number']) * vat_percent)),//(duties * 0.25)
                'vat_on_adjustment' : new Intl.NumberFormat(currency_locale, {
      style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format((parseFloat(importvatfile['adjustment_no']) * vat_percent)),//(adjustment * 0.25)
                'net_amount_commercial_invoice' : net_amount_commercial_invoice,
                'net_amount_sales_invoice' : net_amount_sales_invoice,
                'vat_amount_sales_invoice' : vat_amount_sales_invoice,
                'sales_vat_vs_import_vat' : sales_vat_vs_import_vat,//(vat_amount_sales_invoice - (net_amount * 0.25)) 
                'currency': currency_style,
                'comment_reason': importvatfile['comment_reason'],
                'comment': importvatfile['comment'],
                'comment_visiblity': importvatfile['comment_visiblity'],
               
                'import_vat_xml': importvatfile['xml'],
                'co_invoices': co_invoices,
                'modal_co_invoices': modal_co_invoices
              });               

              control_class = 'first';
             
              declaration_first_start = declaration_first_start + 1;            
            } //has IVF for FIRST TAB            
            else if(ivf_second && (importvatfile['month_year'] == end_month_year))                      
            {   
              let co_invoices = [];
              if(Object.keys(sub_cominvoices).length > 0)
              {
                if($.inArray(importvatfile['month_year'], sub_cominvoices) == -1)
                  co_invoices = sub_cominvoices[importvatfile['month_year']];
              }

              let modal_co_invoices = [];
              if(Object.keys(modal_cominvoices).length > 0)
              {                
                if (modal_cominvoices.hasOwnProperty(importvatfile['month_year']))
                {
                  modal_co_invoices = modal_cominvoices[importvatfile['month_year']];
                  modal_co_invoices.sort((a, b) => a.co_invoice_no.localeCompare(b.co_invoice_no));

                  let filter_other_period_com_invoices_second = modal_cominvoices_other_periods.filter(item => 
                    !modal_cominvoices[importvatfile['month_year']].some(startItem => startItem.id === item.id)
                  );
        
                  modal_co_invoices.push(...filter_other_period_com_invoices_second);
                }                
              }              

              declaration_second_datas = [];
              declaration_second_datas.push({                 
                'id' : importvatfile['id'],   
                'fake_id' : declaration_second_start, 
                'pdf' : "PDF", 
                'declaration_no'  : org_no,
                'o_declaration_date' : '01-' + importvatfile['month_year'], 
                'duties' : new Intl.NumberFormat(currency_locale, {
      style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(importvatfile['fee_number']),     
                'net_amount' : net_amount,
                'adjustment' : new Intl.NumberFormat(currency_locale, {
      style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(importvatfile['adjustment_no']),
                'statistical_value' : statistical_value,
                'import_vat' : new Intl.NumberFormat(currency_locale, {
      style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(((parseFloat(importvatfile['fee_number']) + parseFloat(importvatfile['statistical_number'])) * vat_percent)),//((duties + statistical_value) * 0.25)
                'vat_on_duties' : new Intl.NumberFormat(currency_locale, {
      style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format((parseFloat(importvatfile['fee_number']) * vat_percent)),//(duties * 0.25)
                'vat_on_adjustment' : new Intl.NumberFormat(currency_locale, {
      style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format((parseFloat(importvatfile['adjustment_no']) * vat_percent)),//(adjustment * 0.25)
                'net_amount_commercial_invoice' : net_amount_commercial_invoice,
                'net_amount_sales_invoice' : net_amount_sales_invoice,
                'vat_amount_sales_invoice' : vat_amount_sales_invoice,
                'sales_vat_vs_import_vat' : sales_vat_vs_import_vat,//(vat_amount_sales_invoice - (net_amount * 0.25)) 
                'currency': currency_style,
                'comment_reason': importvatfile['comment_reason'],
                'comment': importvatfile['comment'],
                'comment_visiblity': importvatfile['comment_visiblity'],                
                'import_vat_xml': importvatfile['xml'],     
                'co_invoices': co_invoices,
                'modal_co_invoices': modal_co_invoices
              });
             
              control_class = 'second';
              
              declaration_second_start = declaration_second_start + 1;            
            } //has IVF for SECOND TAB
            
            control_html += '<ul class="p-0 m-0 '+ control_class +'">' +
                                control_li_html +
                              '</ul>';
          } //only XML
        });

        $(".form-declaration-control .declaration-control").html(control_html);

        return {
          'declaration_first_datas' : declaration_first_datas, 
          'declaration_second_datas' : declaration_second_datas
        };  
*/            
      }//declaration 
      else if(type == 'mailbox')
      {
        var mailboxfiles = result['mailboxfiles'];
        
        mailboxfile_new_datas = [];
        mailboxfile_active_datas = [];
        mailboxfile_dismissed_datas = [];

        var mailboxfile_new_start = 1;
        var mailboxfile_active_start = 1;
        var mailboxfile_dismissed_start = 1;
        //var invoice_pdf = '';
       
        // var client_api_name = $("#client_api_name").val();
        // var client_currency_code = $("#currency_code").val();
        // var client_country = $("#client_country").val();
       
        // if(client_currency_code == '')
        // {
        //   if(client_country == "DK" || client_country == "NO")
        //     client_currency_code = "DKK";              
        //   else if(client_country == "SE") 
        //     client_currency_code = "SEK";      
        //   else if(client_country == "GB")
        //     client_currency_code = "GBP";      
        //   else if(client_country == "IN")  
        //     client_currency_code = "INR";  
        //   else if(client_country == "FR") 
        //     client_currency_code = "EUR"; 
        //   else if(client_country == "CH") 
        //     client_currency_code = "CHF";       
        // }

        $.each(mailboxfiles, function (idx, mailboxfile) { 
                   
          if(mailboxfile['status'] == 2)
          {                                                
            mailboxfile_new_datas.push({                 
              'id' : mailboxfile['id'],   
              'fake_id' : mailboxfile_new_start, 
              'client_name' : mailboxfile['vatregmain']['client']['client_name'],
              'email_datetime' : (mailboxfile['email_datetime']) ? moment(mailboxfile['email_datetime']).format('DD-MM-YYYY hh:mm:s A') : '-',
              'email_id' : mailboxfile['email_id'],  
              'email_subject' : mailboxfile['email_subject'],                
              'o_file_name' : mailboxfile['o_file_name'],
              'preview' : 'Preview',
              'file_id' :   mailboxfile['file_id'],
              'vatreg' :   mailboxfile['vatregmain']['vatreg']                
            });
            mailboxfile_new_start = mailboxfile_new_start + 1;
          }

          if(mailboxfile['status'] == 1)
          { 
            mailboxfile_active_datas.push({                 
              'id' : mailboxfile['id'],   
              'fake_id' : mailboxfile_active_start, 
              'client_name' : mailboxfile['vatregmain']['client']['client_name'],
              'email_datetime' : (mailboxfile['email_datetime']) ? moment(mailboxfile['email_datetime']).format('DD-MM-YYYY hh:mm:s A') : '-',
              'email_subject' : mailboxfile['email_subject'],   
              'email_id' : mailboxfile['email_id'],               
              'o_file_name' : mailboxfile['o_file_name'],
              'preview' : 'Preview',
              'file_id' :   mailboxfile['file_id']                
            });
            mailboxfile_active_start = mailboxfile_active_start + 1;    
          }  

          if(mailboxfile['status'] == 0)          
          {
            mailboxfile_dismissed_datas.push({                 
              'id' : mailboxfile['id'],   
              'fake_id' : mailboxfile_dismissed_start, 
              'client_name' : mailboxfile['vatregmain']['client']['client_name'],
              'email_datetime' : (mailboxfile['email_datetime']) ? moment(mailboxfile['email_datetime']).format('DD-MM-YYYY hh:mm:s A') : '-',
              'email_subject' : mailboxfile['email_subject'],     
              'email_id' : mailboxfile['email_id'],             
              'o_file_name' : mailboxfile['o_file_name'],
              'preview' : 'Preview',
              'file_id' :   mailboxfile['file_id']                
            });
            mailboxfile_dismissed_start = mailboxfile_dismissed_start + 1;   
          }                   
        });
 
        return {
          'mailboxfile_new_datas' : mailboxfile_new_datas, 
          'mailboxfile_active_datas' : mailboxfile_active_datas,
          'mailboxfile_dismissed_datas' : mailboxfile_dismissed_datas
        };
      }//mailbox 
      else if(type == 'cargodeclarationfiles')
      {
        var cargodeclarationfiles_result = result['cargodeclarationfiles'];

        var cargodeclarationfiles = cargodeclarationfiles_result['cargodeclarationfiles'];
        console.log(cargodeclarationfiles);
        cargodeclarationfile_datas = [];
       
        var cargodeclarationfile_start = 1;
      
        $.each(cargodeclarationfiles, function (idx, cargodeclarationfile) { 
                   
          if(cargodeclarationfile['status'] == 2)
          {                                                
            cargodeclarationfile_datas.push({                 
              'id' : cargodeclarationfile['id'],   
              'fake_id' : cargodeclarationfile_start, 
              'client_name' : cargodeclarationfiles_result['vatreg']['vatregmain']['client']['client_name'],
              'cargo_date' : (cargodeclarationfile['cargo_date']) ? moment(cargodeclarationfile['cargo_date']).format('DD-MM-YYYY') : '-',
              'expo_no' : cargodeclarationfile['expo_no'],
              'lope_no' : cargodeclarationfile['run_no'],  
              'email_datetime' : (cargodeclarationfile['email_datetime']) ? moment(cargodeclarationfile['email_datetime']).format('DD-MM-YYYY hh:mm:s A') : '-',
              'email_id' : cargodeclarationfile['email_id'],  
              'email_subject' : cargodeclarationfile['email_subject'],                
              'o_file_name' : cargodeclarationfile['o_file_name'],
              'preview' : 'Preview',
              'file_id' :   cargodeclarationfile['file_id']                
            });
            cargodeclarationfile_start = cargodeclarationfile_start + 1;
          }        
        });

        return cargodeclarationfile_datas;
      }//cargodeclarationfiles
      else if(type == 'vatcheck')
      {
        var unmatched_invoices = result['unmatched_invoices'];
        
        unmatched_invoice_datas = [];
        var unmatched_invoice_start = 1;
        $.each(unmatched_invoices, function (idx, invoice) { 
          // var dvuser = user['dvuser'];
          // var role = user['roles'][0];  
          // var userclients = user['userclient'];   

          unmatched_invoice_datas.push({                             
            'id' : user['id'],
            'fake_id' : unmatched_invoice_start,
            'invoice_date' : moment(invoice['invoice_date']).format('DD-MM-YYYY'),
            'invoice_no' : invoice['invoice_no'],
            'currency_code' : invoice['currency_code'],
            'total_net' : new Intl.NumberFormat(currency_locale, {
style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(invoice['total_net']),
            'vat_rate' : Number(invoice['vat_rate']) + '%',
            'total_vat' : new Intl.NumberFormat(currency_locale, {
style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(invoice['total_vat']),
            'total_gross' : new Intl.NumberFormat(currency_locale, {
style: 'decimal', currency: currency_style, minimumFractionDigits: 2, maximumFractionDigits: 2}).format(invoice['total_gross']),
          });
          unmatched_invoice_start = unmatched_invoice_start + 1;
        });
       
        return unmatched_invoice_datas;
      }//vatcheck 
      /*else if(type == 'analyzepdf')
      { console.log(result);
        var vatregmains = result['vatregmains'];
        var analyzepdfs = result['analyzepdfs'];
                
        //analyzepdf_datas = [];
        analyzepdf_completed_datas = [];
        analyzepdf_processing_datas = [];
        analyzepdf_error_datas = [];

        var analyzepdf_completed_start = 1;
        var analyzepdf_processing_start = 1;
        var analyzepdf_error_start = 1;
        console.log(vatregmains);
        $.each(analyzepdfs, function (idx, analyzepdf) {
          
          var parsed_extracted_data = (analyzepdf['extracted_data']) ? JSON.parse(analyzepdf['extracted_data']) : null;
          var invoice_type = analyzepdf.invoice_type;
          var invoice_type_name = '';
          var client_name = '';
          let org_no = null;

          if(parsed_extracted_data)
          {
            if(parsed_extracted_data.supplier)
            {
              //invoice_type = 'sales';
              invoice_type_name = 'Sales Invoice' + (parsed_extracted_data.credit_note ? '(CN)' : '');
           
              // org_no = (parsed_extracted_data.supplier.org_number) ? parsed_extracted_data.supplier.org_number.replace(/[a-zA-Z\s]+/g, '') :
              //         ((parsed_extracted_data.supplier.cvr_number) ?  parsed_extracted_data.supplier.cvr_number.replace(/[a-zA-Z\s]+/g, '') : '');
                           
              const getNumeric = str => str ? str.replace(/\D/g, '') : '';
              let vat_numeric = getNumeric( (parsed_extracted_data.supplier.org_number) ? parsed_extracted_data.supplier.org_number.replace(/[a-zA-Z\s]+/g, '') :
                            ((parsed_extracted_data.supplier.cvr_number) ?  parsed_extracted_data.supplier.cvr_number.replace(/[a-zA-Z\s]+/g, '') : '')
                           );
              
              if (vat_numeric && vat_numeric.length == 17) {
                  org_no = vat_numeric.substring(0, 9);
              }
              else
              {
                if (vat_numeric && vat_numeric.length >= 9)
                  org_no = vat_numeric;      
              }

              var filter_vatregmains = vatregmains.filter(function(vatregmain) {                                        
                  return (vatregmain.org_no && org_no) ? (vatregmain.org_no.replace(/[a-zA-Z\s]+/g, '') === org_no) : false;
              });              

              if(filter_vatregmains.length > 0)
                client_name = filter_vatregmains[0].client.client_name;
              else
                client_name = parsed_extracted_data.supplier.name;
            }
            else if(parsed_extracted_data.recipient)
            {
              //invoice_type = 'com';
              invoice_type_name = 'Commercial Invoice';
                            
              // // First, check CVR number
              // let cvr_numeric = getNumeric(parsed_extracted_data.recipient.cvr_number);

              // // If CVR is 8 digits, skip and check VAT number
              // if (cvr_numeric.length >= 9) {
              //     org_no = cvr_numeric;
              // } else {
              //     let vat_numeric = getNumeric(parsed_extracted_data.recipient.vat_number);
              //     if (vat_numeric.length >= 9) {
              //         org_no = vat_numeric;
              //     }
              // }

              // let vat_numeric = getNumeric(parsed_extracted_data.recipient.org_number);
              // if (vat_numeric.length >= 9) {
              //     org_no = vat_numeric;
              // }

              const getNumeric = str => str ? str.replace(/\D/g, '') : '';
              let vat_numeric = getNumeric( (parsed_extracted_data.recipient.org_number) ? parsed_extracted_data.recipient.org_number.replace(/[a-zA-Z\s]+/g, '') : '');
              
              if (vat_numeric && vat_numeric.length == 17) {
                  org_no = vat_numeric.substring(0, 9);
              }
              else
              {
                if (vat_numeric && vat_numeric.length >= 9)
                  org_no = vat_numeric;      
              }

              var filter_vatregmains = vatregmains.filter(function(vatregmain) {                                        
                return (vatregmain.org_no) ? (vatregmain.org_no.replace(/[a-zA-Z\s]+/g, '') === org_no) : false;
              });

              if(filter_vatregmains.length > 0)
                client_name = filter_vatregmains[0].client.client_name;
              else
                client_name = parsed_extracted_data.recipient.name;
            }
            else
            {
              if(analyzepdf.invoice_type == 'multi-invoices')
              {                
                //invoice_type = 'multi-invoices';
                invoice_type_name = 'Multi Invoices' + ((parsed_extracted_data.length > 0) ? (parsed_extracted_data[0].credit_note ? '(CN)' : '') : '');

                client_name = (parsed_extracted_data.length > 0) ? parsed_extracted_data[0].supplier.name : '';
// console.log(parsed_extracted_data);
//                 $.each(parsed_extracted_data, function (eidx, extracted_data) {
//                   invoice_nos += (invoice_nos == '') ? extracted_data.invoice_number : ('<br>' + extracted_data.invoice_number);
//                 });
              }
            }
          }

          if(invoice_type == '')
          {            
            if(analyzepdf.invoice_type == 'sales')
            {
              //invoice_type = 'sales';
              invoice_type_name = 'Sales Invoice';
            }
            else if(analyzepdf.invoice_type == 'com')
            {
              //invoice_type = 'com';
              invoice_type_name = 'Commercial Invoice';
            }
            else if(analyzepdf.invoice_type == 'multi-invoices')
            {
              //invoice_type = 'multi-invoices';
              invoice_type_name = 'Multi Invoices';
            }            
          }

          if(analyzepdf['status'] === 'completed' && parsed_extracted_data)
          {              
            if (parsed_extracted_data.length === undefined)
            {
              analyzepdf_completed_datas.push({                 
                  'id' : analyzepdf['id'],   
                  'fake_id' : analyzepdf_completed_start,
                  'invoice_no' : parsed_extracted_data.invoice_number,
                  'invoice_type' : invoice_type,
                  'invoice_type_name' : invoice_type_name,
                  'client_name' : client_name,
                  'file_name' : analyzepdf['file_name'],
                  'datetime' : (analyzepdf['created_at']) ? moment(analyzepdf['created_at']).format('DD-MM-YYYY hh:mm:s A') : '-',            
                  'status' : analyzepdf['status'],
                  'start_pageno' : analyzepdf['start_pageno'],
                  'azure_url' : analyzepdf['azure_url'],                  
                  'extracted_data' : analyzepdf['extracted_data']
                });
                analyzepdf_completed_start = analyzepdf_completed_start + 1;
            }
            else
            {
              $.each(parsed_extracted_data, function (eidx, extracted_data) {
                analyzepdf_completed_datas.push({                 
                  'id' : analyzepdf['id'],   
                  'fake_id' : analyzepdf_completed_start,
                  'invoice_no' : extracted_data.invoice_number,
                  'invoice_type' : invoice_type,
                  'invoice_type_name' : invoice_type_name,
                  'client_name' : client_name,
                  'file_name' : analyzepdf['file_name'],
                  'datetime' : (analyzepdf['created_at']) ? moment(analyzepdf['created_at']).format('DD-MM-YYYY hh:mm:s A') : '-',            
                  'status' : analyzepdf['status'],
                  'start_pageno' : analyzepdf['start_pageno'],
                  'azure_url' : analyzepdf['azure_url'],                  
                  'extracted_data' : analyzepdf['extracted_data']
                });
                analyzepdf_completed_start = analyzepdf_completed_start + 1;
              });
            }
          } //completed
          else if(analyzepdf['status'] === 'processing' || analyzepdf['status'] === 'queued')
          {  
            analyzepdf_processing_datas.push({                 
              'id' : analyzepdf['id'],   
              'fake_id' : analyzepdf_processing_start,
              'invoice_no' : '-',
              'invoice_type' : invoice_type,
              'invoice_type_name' : invoice_type_name,
              'client_name' : client_name,
              'file_name' : analyzepdf['file_name'],
              'datetime' : (analyzepdf['created_at']) ? moment(analyzepdf['created_at']).format('DD-MM-YYYY hh:mm:s A') : '-',            
              'status' : analyzepdf['status'],
              'start_pageno' : analyzepdf['start_pageno'],
              'azure_url' : analyzepdf['azure_url'],              
              'extracted_data' : analyzepdf['extracted_data']
            });
            analyzepdf_processing_start = analyzepdf_processing_start + 1; 
          } //processing
          else if(analyzepdf['status'] === 'failed')
          {  
            analyzepdf_error_datas.push({                 
              'id' : analyzepdf['id'],   
              'fake_id' : analyzepdf_error_start,
              'invoice_no' : '-', 
              'invoice_type' : invoice_type,
              'invoice_type_name' : invoice_type_name,
              'client_name' : client_name,
              'file_name' : analyzepdf['file_name'],
              'datetime' : (analyzepdf['created_at']) ? moment(analyzepdf['created_at']).format('DD-MM-YYYY hh:mm:s A') : '-',            
              'status' : analyzepdf['status'],
              'start_pageno' : analyzepdf['start_pageno'],
              'azure_url' : analyzepdf['azure_url'],              
              'extracted_data' : analyzepdf['extracted_data']
            });
            analyzepdf_error_start = analyzepdf_error_start + 1;   
          } //error
        });
console.log(analyzepdf_completed_datas);
console.log(analyzepdf_processing_datas);
console.log(analyzepdf_error_datas);
        return {
          'analyzepdf_completed_datas' : analyzepdf_completed_datas, 
          'analyzepdf_processing_datas' : analyzepdf_processing_datas,
          'analyzepdf_error_datas' : analyzepdf_error_datas
        };          
      }//analyzepdf 
      */
      else if(type == 'analyzepdf' || type == 'analyzepdf_search')
      { 
        var vatregmains = result.vatregmains;
        var analyzepdfs = result.analyzepdfs;
                               
        if(type == 'analyzepdf')
        {
          analyzepdf_completed_datas = [];
          analyzepdf_processing_datas = [];
          analyzepdf_error_datas = [];
          analyzepdf_deleted_datas = [];

          var analyzepdf_completed_start = 1;
          var analyzepdf_processing_start = 1;
          var analyzepdf_error_start = 1;
          var analyzepdf_deleted_start = 1;
        }
        else if(type == 'analyzepdf_search')
        {
          analyzepdf_commercial_invoice_datas = [];
          analyzepdf_sales_invoice_datas = [];
          analyzepdf_declaration_datas = [];

          var analyzepdf_commercial_invoice_start = 1;
          var analyzepdf_sales_invoice_start = 1;
          var analyzepdf_declaration_start = 1;
        }
        
        let salesInvoiceMap = {};
        $.each(analyzepdfs, function (idx, item) {
            if (item.invoice_type === 'sales' || item.invoice_type === 'multi-invoices') {
                //let data = item.extracted_data ? JSON.parse(item.extracted_data) : null;
                let data = item.extracted_data
                                ? (typeof item.extracted_data === 'string'
                                    ? JSON.parse(item.extracted_data)
                                    : item.extracted_data)
                                : null;

                if (!data) return;

                let invNo = data.invoice_number ? data.invoice_number.replace('#', '').trim() : null;
                let noInvNo = data.no_invoice_number ? data.no_invoice_number.replace('#', '').trim() : null;

                let client = null;

                if (data.supplier && data.supplier.name) {
                    client = data.supplier.name.trim().toLowerCase();
                }

                if (invNo && noInvNo && client && (
                    (client.indexOf('rainwear') > -1) || 
                    (client.indexOf('engel') > -1) ||
                    (client.indexOf('berendsohn') > -1)
                  )
                ) 
                {
                    //let key = client + "_" + invNo;
                    let key = noInvNo;
                    salesInvoiceMap[key] = invNo;
                }
            }
        });

        $.each(analyzepdfs, function (idx, analyzepdf) {
          
          //var parsed_extracted_data = (analyzepdf.extracted_data) ? JSON.parse(analyzepdf.extracted_data) : null;
          let parsed_extracted_data = analyzepdf.extracted_data
                                ? (typeof analyzepdf.extracted_data === 'string'
                                    ? JSON.parse(analyzepdf.extracted_data)
                                    : analyzepdf.extracted_data)
                                : null;
                                
          var invoice_type = analyzepdf.invoice_type;
          //var invoice_type_name = '';
          
          // let show_delete = false;
          // //if(!analyzepdf.is_deleted && !org_no && !client_name && !invoice_no && !currency && !net_amount)
          // if(!analyzepdf.is_deleted)
          //   show_delete = true;

          let org_no = null;
          let client_name = null;

          let invoice_no = null;
          let invoice_date = null;

          let currency = null;
          let credit_note = null;

          let net_amount = null;
          let vat_rate = null;
          let vat_amount = null;
          let variance_amount = null;
          let freight_amount = null;
          let discount_amount = null;
          let total_amount = null;

          let exchange_currency = null;
          let exchange_rate = null;
          let exchange_net_amount = null;
          let exchange_vat_amount = null;
          let exchange_total_amount = null;
         
          var invoice_type_name = '';
          if(parsed_extracted_data)
          {
            invoice_no = (parsed_extracted_data.invoice_number) ? parsed_extracted_data.invoice_number.replace('#', "") : null;
            invoice_date = parsed_extracted_data.invoice_date;

            currency = (parsed_extracted_data.currency) ? parsed_extracted_data.currency.trim().replace(/[^\w\s]/g, "").substring(0, 3) : null;
            currency = (currency) ? ((currency.toLowerCase() == 'kr') ? 'DKK' : currency) : null;            

            //exchange_currency = (parsed_extracted_data.exchange_currency) ? parsed_extracted_data.exchange_currency.trim().replace(/[^\w\s]/g, "").substring(0, 3) : null;
            exchange_currency = parsed_extracted_data.exchange_currency
                                  ? parsed_extracted_data.exchange_currency
                                      .trim()
                                      .split('/')                // ["DKK", "NOK"]
                                      .pop()                     // "NOK"
                                      .replace(/[^\w\s]/g, "")
                                      .substring(0, 3)
                                  : null;
            exchange_currency = (exchange_currency) ? ((exchange_currency.toLowerCase() == 'kr') ? 'DKK' : exchange_currency) : null;  

            if(invoice_type == 'sales' || invoice_type == 'multi-invoices')
            {
              if(parsed_extracted_data.supplier)
              {                                        
                const getNumeric = str => str ? str.replace(/\D/g, '') : '';
                let vat_numeric = getNumeric( (parsed_extracted_data.supplier.org_number) ? parsed_extracted_data.supplier.org_number.replace(/[a-zA-Z\s]+/g, '') :
                              ((parsed_extracted_data.supplier.cvr_number) ?  parsed_extracted_data.supplier.cvr_number.replace(/[a-zA-Z\s]+/g, '') : '')
                             );
                
                if (vat_numeric && vat_numeric.length == 17) 
                {
                    org_no = vat_numeric.substring(0, 9);
                }
                else
                {
                  if (vat_numeric && (vat_numeric.length >= 9 || vat_numeric.length == 8))
                    org_no = vat_numeric;      
                }

                var filter_vatregmains = vatregmains.filter(function(vatregmain) {                                        
                    return ((vatregmain.org_no && org_no) ? (vatregmain.org_no.replace(/[a-zA-Z\s]+/g, '') === org_no) : false ||
                      (vatregmain.vat_no && org_no) ? (vatregmain.vat_no.replace(/[a-zA-Z\s]+/g, '') === org_no) : false);
                });              

                if(filter_vatregmains.length > 0)
                  client_name = filter_vatregmains[0].client.client_name;
                else
                  client_name = parsed_extracted_data.supplier.name;

                if (client_name && client_name.toLowerCase().indexOf('dfi-geisler') > -1)
                  invoice_no = (invoice_no) ? invoice_no : ((invoice_date) ? invoice_date.replace(/-/g, '') : null);

                if (client_name && (client_name.toLowerCase().indexOf('rainwear') > -1 || client_name.toLowerCase().indexOf('engel') > -1
                   || client_name.toLowerCase().indexOf('berendsohn') > -1)
                )
                  invoice_no = (parsed_extracted_data.no_invoice_number) ? parsed_extracted_data.no_invoice_number : invoice_no;
                
                if (client_name && client_name.toLowerCase().indexOf('stof') > -1)
                  invoice_no = (invoice_no) ? invoice_no.replace(/-/g, '') : invoice_no;
                else if (client_name && client_name.toLowerCase().indexOf('horn bord') > -1)
                  invoice_no = (parsed_extracted_data.order_number) ? parsed_extracted_data.order_number : invoice_no;
              }

              invoice_type_name = 'Sales Invoice' + (parsed_extracted_data.credit_note ? '(CN)' : '');

              credit_note = (parsed_extracted_data.credit_note) ? true : false;

              let og_net_amount = parsed_extracted_data.net_amount ? parsed_extracted_data.net_amount.replace(/[a-zA-Z\s]+/g, '') : '';
              let og_vat_amount = parsed_extracted_data.vat_amount ? parsed_extracted_data.vat_amount.replace(/[a-zA-Z\s]+/g, '') : '';
              let og_variance_amount = parsed_extracted_data.variance ? parsed_extracted_data.variance.replace(/[a-zA-Z\s]+/g, '') : '';
              let og_freight_amount = parsed_extracted_data.additional_charges ? parsed_extracted_data.additional_charges.replace(/[a-zA-Z\s]+/g, '') : '';
              let og_discount_amount = parsed_extracted_data.discount_amount ? parsed_extracted_data.discount_amount.replace(/[a-zA-Z\s]+/g, '') : '';       
              let og_total_amount = parsed_extracted_data.total_amount ? parsed_extracted_data.total_amount.replace(/[a-zA-Z\s]+/g, '') : '';             
                            
              let og_exchange_rate = parsed_extracted_data.exchange_rate ? parsed_extracted_data.exchange_rate.replace(/[a-zA-Z\s]+/g, '') : '';
              let og_exchange_net_amount = parsed_extracted_data.exchange_net_amount ? parsed_extracted_data.exchange_net_amount.replace(/[a-zA-Z\s]+/g, '') : '';
              let og_exchange_vat_amount = parsed_extracted_data.exchange_vat_amount ? parsed_extracted_data.exchange_vat_amount.replace(/[a-zA-Z\s]+/g, '') : '';

              if(!exchange_currency)
              {
                const exchangeCurrencyPattern = /\b([A-Z]{3})\b/i;

                let detectedExchangeCurrency = null;

                const fieldsToCheck = [
                    parsed_extracted_data.exchange_rate,
                    parsed_extracted_data.exchange_net_amount,
                    parsed_extracted_data.exchange_vat_amount
                ];

                for (const field of fieldsToCheck) {
                    if (field) {
                        const match = field.match(exchangeCurrencyPattern);

                        if (match) {
                            detectedExchangeCurrency = match[1].toUpperCase();
                            break;
                        }
                    }
                }

                if(detectedExchangeCurrency)
                  exchange_currency = detectedExchangeCurrency;
              }

              let parse_exchange_rate = parseAmountValue(og_exchange_rate);
              let parse_exchange_net_amount = parseAmountValue(og_exchange_net_amount);
              let parse_exchange_vat_amount = parseAmountValue(og_exchange_vat_amount);
              
              if (og_discount_amount && /^\d$/.test(og_discount_amount))
                og_discount_amount = '';

              let parse_net_amount = parseAmountValue(og_net_amount);
              let parse_vat_amount = parseAmountValue(og_vat_amount);
              let parse_variance_amount = parseAmountValue(og_variance_amount);
              let parse_freight_amount = parseAmountValue(og_freight_amount);
              let parse_discount_amount = parseAmountValue(og_discount_amount);
              let parse_total_amount = parseAmountValue(og_total_amount);                          

              if(/,(\d{1,2})$/.test(og_net_amount))
              {
                parse_net_amount = parseAmountValue(og_net_amount, 'NOK');
                parse_vat_amount = parseAmountValue(og_vat_amount, 'NOK');
                parse_variance_amount = parseAmountValue(og_variance_amount, 'NOK');
                parse_freight_amount = parseAmountValue(og_freight_amount, 'NOK');
                parse_discount_amount = parseAmountValue(og_discount_amount, 'NOK');
                parse_total_amount = parseAmountValue(og_total_amount, 'NOK');                             
              }   

              if(parse_net_amount)
              {                             
                net_amount = parse_net_amount.toLocaleString('en-IN');               
              } 

              if(parse_freight_amount)
              {              
                let parse_net_freight_amount = parse_net_amount + parse_freight_amount;
                parse_net_amount = parse_net_freight_amount;
                
                net_amount = parse_net_freight_amount.toLocaleString('en-IN');                
              } 

              if(parse_variance_amount)
              {              
                let parse_net_variance_amount = parse_net_amount + parse_variance_amount;
                parse_net_amount = parse_net_variance_amount;
                
                net_amount = parse_net_variance_amount.toLocaleString('en-IN');                
              } 

              if(parse_discount_amount)
              {              
                let parse_sub_discount_amount = parse_net_amount - parse_discount_amount;
                parse_net_amount = parse_sub_discount_amount;
                
                net_amount = parse_sub_discount_amount.toLocaleString('en-IN');
              }  

              if(parse_total_amount != 0 && (parse_net_amount > parse_total_amount))
              {                
                if(parsed_extracted_data.credit_note)
                {
                  let formatted_net_amount = parseDenmarkFormat(og_net_amount);        
                  net_amount = formatted_net_amount;

                  let formatted_total_amount = parseDenmarkFormat(og_total_amount);        
                  total_amount = formatted_total_amount;
                }
                else
                {
                  let formatted_net_amount = parseDenmarkFormat(og_total_amount);        
                  net_amount = formatted_net_amount;

                  let formatted_total_amount = parseDenmarkFormat(og_net_amount);        
                  total_amount = formatted_total_amount;
                }          
              }
              else
              {                
                let formatted_net_amount = parseDenmarkFormat(og_net_amount); 
                if(og_net_amount != net_amount)
                  formatted_net_amount = parseDenmarkFormat(net_amount);  

                net_amount = formatted_net_amount;

                let formatted_total_amount = parseDenmarkFormat(og_total_amount);        
                total_amount = formatted_total_amount;
              }

              let formatted_vat_amount = parseDenmarkFormat(og_vat_amount);        
              vat_amount = formatted_vat_amount;              

              if(parse_total_amount == 0)
              {              
                parse_total_amount = parse_net_amount + parse_vat_amount;
                
                let formatted_total_amount = parseDenmarkFormat(parse_total_amount.toString());        
                total_amount = formatted_total_amount;
              }

              var calculated_vat_rate = (parse_net_amount == 0) ? 0 : ((parse_vat_amount / parse_net_amount) * 100);    
              if(parsed_extracted_data.vat_rate)
              {          
                let og_vat_rate = parseVatRate(parsed_extracted_data.vat_rate);
      
                if(parsed_extracted_data.vat_rate == calculated_vat_rate)
                  vat_rate = og_vat_rate;
                else if(calculated_vat_rate > 25)
                  vat_rate = og_vat_rate;
                else
                {
                  let calculated_vat_rate_result = null;
                  if (calculated_vat_rate >= 8 && calculated_vat_rate < 9)
                    calculated_vat_rate_result = "8,1";
                  else
                    calculated_vat_rate_result = Math.round(calculated_vat_rate).toString();

                  vat_rate = calculated_vat_rate_result;
                }
              }
              else
              {
                let calculated_vat_rate_result = null;
                if (calculated_vat_rate >= 8 && calculated_vat_rate < 9)
                  calculated_vat_rate_result = "8,1";
                else
                  calculated_vat_rate_result = Math.round(calculated_vat_rate).toString();

                vat_rate = calculated_vat_rate_result;
              }

              if(exchange_currency)
              {
                if(/,(\d{1,2})$/.test(og_exchange_net_amount))
                {                  
                  parse_exchange_net_amount = parseAmountValue(og_exchange_net_amount, 'NOK');
                  parse_exchange_vat_amount = parseAmountValue(og_exchange_vat_amount, 'NOK'); 

                  const epsilon = 0.00001;

                  const isNetZero = Math.abs(parse_exchange_net_amount) < epsilon;
                  const isVatZero = Math.abs(parse_exchange_vat_amount) < epsilon;

                  if (vat_rate) {
                      if (isNetZero && !isVatZero) {
                          parse_exchange_net_amount = (parse_exchange_vat_amount / vat_rate) * 100;
                      } else if (isVatZero && !isNetZero) {
                          parse_exchange_vat_amount = (parse_exchange_net_amount * vat_rate) / 100;
                      }
                  }
                  
                  exchange_rate = og_exchange_rate;

                  let formatted_exchange_net_amount = parseDenmarkFormat(parse_exchange_net_amount.toString());        
                  exchange_net_amount = formatted_exchange_net_amount;

                  let formatted_exchange_vat_amount = parseDenmarkFormat(parse_exchange_vat_amount.toString());        
                  exchange_vat_amount = formatted_exchange_vat_amount;

                  let parse_exchange_total_amount = parse_exchange_net_amount + parse_exchange_vat_amount;

                  let formatted_exchange_total_amount = parseDenmarkFormat(parse_exchange_total_amount.toString());        
                  exchange_total_amount = formatted_exchange_total_amount;                  
                }
              }

              if (client_name && (
                  client_name.toLowerCase().indexOf('einhell') > -1 
                  || client_name.toLowerCase().indexOf('woden') > -1
                )
              )
              {
                if(client_name.toLowerCase().indexOf('woden') > -1)
                {
                  let swap_currency = currency;
                  let swap_exchange_currency = exchange_currency; 

                  currency = swap_exchange_currency;
                  exchange_currency = swap_currency;

                  let swap_net_amount = net_amount;
                  let swap_exchange_net_amount = exchange_net_amount;

                  net_amount = swap_exchange_net_amount;
                  exchange_net_amount = swap_net_amount;
                  
                  let swap_vat_amount = vat_amount;
                  let swap_exchange_vat_amount = exchange_vat_amount;

                  vat_amount = swap_exchange_vat_amount;
                  exchange_vat_amount = swap_vat_amount;

                  let swap_total_amount = total_amount;
                  let swap_exchange_total_amount = exchange_total_amount;

                  total_amount = swap_exchange_total_amount;
                  exchange_total_amount = swap_total_amount;
                }
                else
                {
                  if(!exchange_currency && og_exchange_vat_amount)
                  {
                    parse_exchange_vat_amount = parseAmountValue(og_exchange_vat_amount, 'NOK');

                    let calc_exchange_net_amount = (parse_exchange_vat_amount * 100) / vat_rate;
                    let calc_exchange_total_amount = calc_exchange_net_amount + parse_exchange_vat_amount;

                    exchange_currency = currency;
                    currency = 'NOK';

                    exchange_net_amount = net_amount;
                    net_amount = parseDenmarkFormat(calc_exchange_net_amount.toString());

                    exchange_vat_amount = vat_amount;
                    vat_amount = parseDenmarkFormat(parse_exchange_vat_amount.toString());
                    
                    exchange_total_amount = total_amount;
                    total_amount = parseDenmarkFormat(calc_exchange_total_amount.toString());
                  }
                }
              }

              if (credit_note === true && net_amount && !net_amount.startsWith('-'))
                net_amount = '-' + net_amount.trim();

              if (credit_note === true && vat_amount && !vat_amount.startsWith('-'))
                vat_amount = '-' + vat_amount.trim();

              if (credit_note === true && total_amount && !total_amount.startsWith('-'))
                total_amount = '-' + total_amount.trim();    

              if (credit_note === true && exchange_net_amount && !exchange_net_amount.startsWith('-'))
                exchange_net_amount = '-' + exchange_net_amount.trim();

              if (credit_note === true && exchange_vat_amount && !exchange_vat_amount.startsWith('-'))
                exchange_vat_amount = '-' + exchange_vat_amount.trim();

              if (credit_note === true && exchange_total_amount && !exchange_total_amount.startsWith('-'))
                exchange_total_amount = '-' + exchange_total_amount.trim();                

              if(type == 'analyzepdf')
              {
                if(!analyzepdf.is_deleted && analyzepdf.status === 'completed' && parsed_extracted_data)
                {              
                  if (parsed_extracted_data.length === undefined)
                  {
                    //console.log("SALES - completedundefined");
                    analyzepdf_completed_datas.push({
                      'id' : analyzepdf.id,
                      'fake_id' : analyzepdf_completed_start,
                      'invoice_type_name' : invoice_type_name,
                      'client_no' : org_no,
                      'client_name' : (client_name) ? client_name.toUpperCase() : null,
                      'invoice_type' : analyzepdf.invoice_type,
                      'invoice_no' : invoice_no,
                      'invoice_date' : invoice_date,
                      'currency' : currency,
                      'credit_note' : credit_note,
                      'net_amount' : net_amount,
                      'vat_rate' : vat_rate,
                      'vat_amount' : vat_amount,
                      'variance_amount' : variance_amount,
                      'freight_amount' : freight_amount,
                      'discount_amount' : discount_amount,
                      'total_amount' : total_amount,
                      'exchange_currency' : exchange_currency,
                      'exchange_rate' : exchange_rate,
                      'exchange_net_amount' : exchange_net_amount,
                      'exchange_vat_amount' : exchange_vat_amount,
                      'exchange_total_amount' : exchange_total_amount,
                      'azure_url' : analyzepdf.azure_url,
                      'start_pageno' : analyzepdf.start_pageno,
                      'status' : analyzepdf.status,
                      'file_name' : analyzepdf.file_name,
                      'created_at' : (analyzepdf.created_at) ? moment(analyzepdf.created_at).format('DD-MM-YYYY hh:mm A') : '-',
                      'updated_at' : (analyzepdf.updated_at) ? moment(analyzepdf.updated_at).format('DD-MM-YYYY hh:mm A') : '-',
                      'extracted_data' : analyzepdf.extracted_data,
                      'error' : analyzepdf.error,
                      'is_deleted' : analyzepdf.is_deleted,
                      'sync_status' : analyzepdf.sync_status
                    });
                    analyzepdf_completed_start = analyzepdf_completed_start + 1;
                  }
                  else
                  {
                    //console.log("SALES - completed");                    
                    //$.each(parsed_extracted_data, function (eidx, extracted_data) {
                    analyzepdf_completed_datas.push({
                      'id' : analyzepdf.id,
                      'fake_id' : analyzepdf_completed_start,
                      'invoice_type_name' : invoice_type_name,
                      'client_no' : org_no,
                      'client_name' : (client_name) ? client_name.toUpperCase() : null,
                      'invoice_type' : analyzepdf.invoice_type,
                      'invoice_no' : invoice_no,
                      'invoice_date' : invoice_date,
                      'currency' : currency,
                      'credit_note' : credit_note,
                      'net_amount' : net_amount,
                      'vat_rate' : vat_rate,
                      'vat_amount' : vat_amount,
                      'variance_amount' : variance_amount,
                      'freight_amount' : freight_amount,
                      'discount_amount' : discount_amount,
                      'total_amount' : total_amount,
                      'exchange_currency' : exchange_currency,
                      'exchange_rate' : exchange_rate,
                      'exchange_net_amount' : exchange_net_amount,
                      'exchange_vat_amount' : exchange_vat_amount,
                      'exchange_total_amount' : exchange_total_amount,
                      'azure_url' : analyzepdf.azure_url,
                      'start_pageno' : analyzepdf.start_pageno,
                      'status' : analyzepdf.status,
                      'file_name' : analyzepdf.file_name,
                      'created_at' : (analyzepdf.created_at) ? moment(analyzepdf.created_at).format('DD-MM-YYYY hh:mm A') : '-',
                      'updated_at' : (analyzepdf.updated_at) ? moment(analyzepdf.updated_at).format('DD-MM-YYYY hh:mm A') : '-',
                      'extracted_data' : analyzepdf.extracted_data,
                      'error' : analyzepdf.error,
                      'is_deleted' : analyzepdf.is_deleted,
                      'sync_status' : analyzepdf.sync_status
                    });
                    analyzepdf_completed_start = analyzepdf_completed_start + 1;
                    //});
                  }
                } //completed
                else if(!analyzepdf.is_deleted && (analyzepdf.status === 'processing' || analyzepdf.status === 'queued'))
                {  
                  //console.log("SALES - processing");
                  //console.log(analyzepdf);
                  analyzepdf_processing_datas.push({
                    'id' : analyzepdf.id,
                    'fake_id' : analyzepdf_processing_start,
                    'invoice_type_name' : invoice_type_name,
                    'client_no' : org_no,
                    'client_name' : (client_name) ? client_name.toUpperCase() : null,
                    'invoice_type' : analyzepdf.invoice_type,
                    'invoice_no' : invoice_no,
                    'invoice_date' : invoice_date,
                    'currency' : currency,
                    'credit_note' : credit_note,
                    'net_amount' : net_amount,
                    'vat_rate' : vat_rate,
                    'vat_amount' : vat_amount,
                    'variance_amount' : variance_amount,
                    'freight_amount' : freight_amount,
                    'discount_amount' : discount_amount,
                    'total_amount' : total_amount,
                    'exchange_currency' : exchange_currency,
                    'exchange_rate' : exchange_rate,
                    'exchange_net_amount' : exchange_net_amount,
                    'exchange_vat_amount' : exchange_vat_amount,
                    'exchange_total_amount' : exchange_total_amount,
                    'azure_url' : analyzepdf.azure_url,
                    'start_pageno' : analyzepdf.start_pageno,
                    'status' : analyzepdf.status,
                    'file_name' : analyzepdf.file_name,
                    'created_at' : (analyzepdf.created_at) ? moment(analyzepdf.created_at).format('DD-MM-YYYY hh:mm A') : '-',
                    'updated_at' : (analyzepdf.updated_at) ? moment(analyzepdf.updated_at).format('DD-MM-YYYY hh:mm A') : '-',
                    'extracted_data' : analyzepdf.extracted_data,
                    'error' : analyzepdf.error,
                    'is_deleted' : analyzepdf.is_deleted
                  });
                  analyzepdf_processing_start = analyzepdf_processing_start + 1; 
                } //processing
                else if(!analyzepdf.is_deleted && analyzepdf.status === 'failed')
                {  
                  //console.log("SALES - failed");                 
                  analyzepdf_error_datas.push({
                    'id' : analyzepdf.id,
                    'fake_id' : analyzepdf_error_start,
                    'invoice_type_name' : invoice_type_name,
                    'client_no' : org_no,
                    'client_name' : (client_name) ? client_name.toUpperCase() : null,
                    'invoice_type' : analyzepdf.invoice_type,
                    'invoice_no' : invoice_no,
                    'invoice_date' : invoice_date,
                    'currency' : currency,
                    'credit_note' : credit_note,
                    'net_amount' : net_amount,
                    'vat_rate' : vat_rate,
                    'vat_amount' : vat_amount,
                    'variance_amount' : variance_amount,
                    'freight_amount' : freight_amount,
                    'discount_amount' : discount_amount,
                    'total_amount' : total_amount,
                    'exchange_currency' : exchange_currency,
                    'exchange_rate' : exchange_rate,
                    'exchange_net_amount' : exchange_net_amount,
                    'exchange_vat_amount' : exchange_vat_amount,
                    'exchange_total_amount' : exchange_total_amount,
                    'azure_url' : analyzepdf.azure_url,
                    'start_pageno' : analyzepdf.start_pageno,
                    'status' : analyzepdf.status,
                    'file_name' : analyzepdf.file_name,
                    'created_at' : (analyzepdf.created_at) ? moment(analyzepdf.created_at).format('DD-MM-YYYY hh:mm A') : '-',
                    'updated_at' : (analyzepdf.updated_at) ? moment(analyzepdf.updated_at).format('DD-MM-YYYY hh:mm A') : '-',
                    'extracted_data' : analyzepdf.extracted_data,
                    'error' : analyzepdf.error,
                    'is_deleted' : analyzepdf.is_deleted
                  });
                  analyzepdf_error_start = analyzepdf_error_start + 1;
                } //error  

                if(analyzepdf.is_deleted || analyzepdf.status === 'duplicate')
                {  
                  analyzepdf_deleted_datas.push({
                    'id' : analyzepdf.id,
                    'fake_id' : analyzepdf_deleted_start,
                    'invoice_type_name' : invoice_type_name,
                    'client_no' : org_no,
                    'client_name' : (client_name) ? client_name.toUpperCase() : null,
                    'invoice_type' : analyzepdf.invoice_type,
                    'invoice_no' : invoice_no,
                    'invoice_date' : invoice_date,
                    'currency' : currency,
                    'credit_note' : credit_note,
                    'net_amount' : net_amount,
                    'vat_rate' : vat_rate,
                    'vat_amount' : vat_amount,
                    'variance_amount' : variance_amount,
                    'freight_amount' : freight_amount,
                    'discount_amount' : discount_amount,
                    'total_amount' : total_amount,
                    'exchange_currency' : exchange_currency,
                    'exchange_rate' : exchange_rate,
                    'exchange_net_amount' : exchange_net_amount,
                    'exchange_vat_amount' : exchange_vat_amount,
                    'exchange_total_amount' : exchange_total_amount,
                    'azure_url' : analyzepdf.azure_url,
                    'start_pageno' : analyzepdf.start_pageno,
                    'status' : analyzepdf.status,
                    'file_name' : analyzepdf.file_name,
                    'created_at' : (analyzepdf.created_at) ? moment(analyzepdf.created_at).format('DD-MM-YYYY hh:mm A') : '-',
                    'updated_at' : (analyzepdf.updated_at) ? moment(analyzepdf.updated_at).format('DD-MM-YYYY hh:mm A') : '-',
                    'extracted_data' : analyzepdf.extracted_data,
                    'error' : analyzepdf.error,
                    'is_deleted' : analyzepdf.is_deleted,
                    'deleted_reason' : analyzepdf.deleted_reason,
                    'duplicate_message' : analyzepdf.duplicate_message
                  });
                  analyzepdf_deleted_start = analyzepdf_deleted_start + 1;   
                } //deleted
              } //capture
              else if(type == 'analyzepdf_search')
              {
                if(!analyzepdf.is_deleted && analyzepdf.sync_status == 1)
                {
                  analyzepdf_sales_invoice_datas.push({                 
                    'id' : analyzepdf.id,
                    'fake_id' : analyzepdf_sales_invoice_start,
                    'invoice_type_name' : invoice_type_name,
                    'client_no' : org_no,
                    'client_name' : (client_name) ? client_name.toUpperCase() : null,
                    'invoice_type' : analyzepdf.invoice_type,
                    'invoice_no' : invoice_no,
                    'invoice_date' : invoice_date,
                    'currency' : currency,
                    'credit_note' : credit_note,
                    'net_amount' : net_amount,
                    'vat_rate' : vat_rate,
                    'vat_amount' : vat_amount,
                    'variance_amount' : variance_amount,
                    'freight_amount' : freight_amount,
                    'discount_amount' : discount_amount,
                    'total_amount' : total_amount,
                    'exchange_currency' : exchange_currency,
                    'exchange_rate' : exchange_rate,
                    'exchange_net_amount' : exchange_net_amount,
                    'exchange_vat_amount' : exchange_vat_amount,
                    'exchange_total_amount' : exchange_total_amount,
                    'azure_url' : analyzepdf.azure_url,
                    'start_pageno' : analyzepdf.start_pageno,
                    'status' : analyzepdf.status,
                    'file_name' : analyzepdf.file_name,
                    'created_at' : (analyzepdf.created_at) ? moment(analyzepdf.created_at).format('DD-MM-YYYY hh:mm A') : '-',
                    'updated_at' : (analyzepdf.updated_at) ? moment(analyzepdf.updated_at).format('DD-MM-YYYY hh:mm A') : '-',
                    'error' : analyzepdf.error,
                    'is_deleted' : analyzepdf.is_deleted,
                    'extracted_data' : analyzepdf.extracted_data
                  });
                  analyzepdf_sales_invoice_start = analyzepdf_sales_invoice_start + 1;
                } //deleted
              } //search
            } //sales
            else if(invoice_type == 'com')
            {
              invoice_type_name = 'Commercial Invoice';
              
              let og_net_amount = parsed_extracted_data.net_amount ? parsed_extracted_data.net_amount.replace(/[a-zA-Z\s]+/g, '') : '';
              let parse_net_amount = parseAmountValue(og_net_amount);

              //if(/,(\d{1,2})$/.test(og_net_amount))           
                //parse_net_amount = parseAmountValue(og_net_amount, 'NOK');

              let formatted_net_amount = parseDenmarkFormat(og_net_amount);        
              net_amount = formatted_net_amount;
              
              let og_exchange_net_amount = parsed_extracted_data.exchange_net_amount ? parsed_extracted_data.exchange_net_amount.replace(/[a-zA-Z\s]+/g, '') : '';              
              if(!exchange_currency)
              {
                const exchangeCurrencyPattern = /\b([A-Z]{3})\b/i;

                let detectedExchangeCurrency = null;

                const fieldsToCheck = [
                    parsed_extracted_data.exchange_rate,
                    parsed_extracted_data.exchange_net_amount,
                    parsed_extracted_data.exchange_vat_amount
                ];

                for (const field of fieldsToCheck) {
                    if (field) {
                        const match = field.match(exchangeCurrencyPattern);

                        if (match) {
                            detectedExchangeCurrency = match[1].toUpperCase();
                            break;
                        }
                    }
                }

                if(detectedExchangeCurrency)
                  exchange_currency = detectedExchangeCurrency;
              }              
              let parse_exchange_net_amount = parseAmountValue(og_exchange_net_amount);              

              if(exchange_currency)
              {
                if(/,(\d{1,2})$/.test(og_exchange_net_amount))
                {                  
                  parse_exchange_net_amount = parseAmountValue(og_exchange_net_amount, 'NOK');
                  //parse_exchange_vat_amount = parseAmountValue(og_exchange_vat_amount, 'NOK'); 

                  //const epsilon = 0.00001;

                  //const isNetZero = Math.abs(parse_exchange_net_amount) < epsilon;
                  //const isVatZero = Math.abs(parse_exchange_vat_amount) < epsilon;

                  // if (vat_rate) {
                  //     if (isNetZero && !isVatZero) {
                  //         parse_exchange_net_amount = (parse_exchange_vat_amount / vat_rate) * 100;
                  //     } else if (isVatZero && !isNetZero) {
                  //         parse_exchange_vat_amount = (parse_exchange_net_amount * vat_rate) / 100;
                  //     }
                  // }
                  
                  // exchange_rate = og_exchange_rate;

                  let formatted_exchange_net_amount = parseDenmarkFormat(parse_exchange_net_amount.toString());        
                  exchange_net_amount = formatted_exchange_net_amount;

                  // let formatted_exchange_vat_amount = parseDenmarkFormat(parse_exchange_vat_amount.toString());        
                  // exchange_vat_amount = formatted_exchange_vat_amount;

                  // let parse_exchange_total_amount = parse_exchange_net_amount + parse_exchange_vat_amount;

                  // let formatted_exchange_total_amount = parseDenmarkFormat(parse_exchange_total_amount.toString());        
                  // exchange_total_amount = formatted_exchange_total_amount;                  
                }
              }

              if(parsed_extracted_data.recipient)
              {                                                    
                const getNumeric = str => str ? str.replace(/\D/g, '') : '';
                let vat_numeric = getNumeric( (parsed_extracted_data.recipient.org_number) ? parsed_extracted_data.recipient.org_number.replace(/[a-zA-Z\s]+/g, '') : '');
                
                if (vat_numeric && vat_numeric.length == 17) 
                {
                    org_no = vat_numeric.substring(0, 9);
                }
                else
                {
                  if (vat_numeric && (vat_numeric.length >= 9 || vat_numeric.length == 8))
                    org_no = vat_numeric;      
                }

                var filter_vatregmains = vatregmains.filter(function(vatregmain) {                                        
                  return ((vatregmain.org_no) ? (vatregmain.org_no.replace(/[a-zA-Z\s]+/g, '') === org_no) : false ||
                    (vatregmain.vat_no) ? (vatregmain.vat_no.replace(/[a-zA-Z\s]+/g, '') === org_no) : false);
                });

                if(filter_vatregmains.length > 0)
                  client_name = filter_vatregmains[0].client.client_name;
                else
                  client_name = parsed_extracted_data.recipient.name;

                if (client_name && client_name.toLowerCase().indexOf('dfi-geisler') > -1)
                  invoice_no = (invoice_no) ? invoice_no : ((invoice_date) ? invoice_date.replace(/-/g, '') : null);                
              }

              var related_sales_invoices = null;
              var sales_invoices_raw = (parsed_extracted_data) ? parsed_extracted_data.related_sales_invoices : null;
              if (sales_invoices_raw) 
              {
                  if (!Array.isArray(sales_invoices_raw)) {
                      sales_invoices_raw = [sales_invoices_raw];
                  }

                  var invoiceValues = new Set();

                  sales_invoices_raw.forEach(function(val) {
                      if (!val) return;

                      // Split by commas first
                      var commaParts = String(val).split(',');

                      commaParts.forEach(function(part) {                    
                          part = part.trim().replace(/[.,;]+$/, '');
                          if (!part) return;

                          // Match alphanumeric or numeric range first (with optional spaces around dash)
                          var rangeMatch = part.match(/^([A-Za-z]*)(\d+)\s*-\s*([A-Za-z]*)(\d+)$/);

                          if (rangeMatch) {
                              var prefixStart = rangeMatch[1];
                              var startNum = parseInt(rangeMatch[2], 10);
                              var prefixEnd = rangeMatch[3];
                              var endNum = parseInt(rangeMatch[4], 10);

                              // Handle shorthand ranges like 8992-99
                              if (endNum.toString().length < startNum.toString().length) {
                                var startStr = startNum.toString();
                                var endStr = endNum.toString();
                                endStr = startStr.slice(0, startStr.length - endStr.length) + endStr;

                                startNum = parseInt(startStr, 10);
                                endNum = parseInt(endStr, 10);
                              }

                              if (prefixStart === prefixEnd && startNum <= endNum) {
                                  for (var i = startNum; i <= endNum; i++) {
                                      invoiceValues.add(
                                          prefixStart + i.toString().padStart(rangeMatch[2].length, '0')
                                      );
                                  }
                              }
                          } else {
                              // Not a range: split by spaces (for "123 124 125" or "NO123 NO124")
                              part.split(/\s+/).forEach(function(p) {
                                  if (p) invoiceValues.add(p);
                              });
                          }
                      });
                  });
                
                  // Optional: convert to array and sort numerically/alphabetically
                  related_sales_invoices = Array.from(invoiceValues).sort((a,b) => {
                      var numA = parseInt(a.replace(/\D+/g,''), 10);
                      var numB = parseInt(b.replace(/\D+/g,''), 10);
                      return (numA && numB) ? numA - numB : a.localeCompare(b);
                  });
              }  

              if (client_name && client_name.toLowerCase().indexOf('rainwear') > -1)
              {                
                if (related_sales_invoices && related_sales_invoices.length) 
                {
                  let clientKey = client_name.trim().toLowerCase();

                  let matchedInvoice = null;

                  related_sales_invoices.forEach(function(inv) {
                      let cleanInv = inv.trim();
                      //let key = clientKey + "_" + cleanInv;
                      let key = cleanInv;

                      if (salesInvoiceMap[key] && !matchedInvoice) {
                          matchedInvoice = salesInvoiceMap[key];
                      }
                  });

                  if (matchedInvoice) {
                      // Use matched sales invoice number
                      invoice_no = matchedInvoice;
                  }
                }
              } //rainwear

              if(type == 'analyzepdf')
              {
                if(!analyzepdf.is_deleted && analyzepdf.status === 'completed' && parsed_extracted_data)
                {              
                  if (parsed_extracted_data.length === undefined)
                  {
                    //console.log("COM - completed undefined");
                    analyzepdf_completed_datas.push({
                      'id' : analyzepdf.id,
                      'fake_id' : analyzepdf_completed_start,
                      'invoice_type_name' : invoice_type_name,
                      'client_no' : org_no,
                      'client_name' : (client_name) ? client_name.toUpperCase() : null,
                      'invoice_type' : analyzepdf.invoice_type,                 
                      'invoice_no' : invoice_no,
                      'invoice_date' : invoice_date,
                      'currency' : currency,                  
                      'net_amount' : net_amount,   
                      'exchange_currency' : exchange_currency,
                      'exchange_net_amount' : exchange_net_amount,
                      'related_sales_invoices' : related_sales_invoices,
                      'azure_url' : analyzepdf.azure_url,
                      'start_pageno' : analyzepdf.start_pageno,
                      'status' : analyzepdf.status,
                      'file_name' : analyzepdf.file_name,
                      'created_at' : (analyzepdf.created_at) ? moment(analyzepdf.created_at).format('DD-MM-YYYY hh:mm A') : '-',
                      'updated_at' : (analyzepdf.updated_at) ? moment(analyzepdf.updated_at).format('DD-MM-YYYY hh:mm A') : '-',
                      'extracted_data' : analyzepdf.extracted_data,
                      'error' : analyzepdf.error,
                      'is_deleted' : analyzepdf.is_deleted,
                      'sync_status' : analyzepdf.sync_status
                    });
                    analyzepdf_completed_start = analyzepdf_completed_start + 1;                    
                  }
                  else
                  {
                    //console.log("COM - completed");
                    //$.each(parsed_extracted_data, function (eidx, extracted_data) {
                    analyzepdf_completed_datas.push({
                      'id' : analyzepdf.id,
                      'fake_id' : analyzepdf_completed_start,
                      'invoice_type_name' : invoice_type_name,
                      'client_no' : org_no,
                      'client_name' : (client_name) ? client_name.toUpperCase() : null,
                      'invoice_type' : analyzepdf.invoice_type,                 
                      'invoice_no' : invoice_no,
                      'invoice_date' : invoice_date,
                      'currency' : currency,                  
                      'net_amount' : net_amount,  
                      'exchange_currency' : exchange_currency,
                      'exchange_net_amount' : exchange_net_amount,                
                      'related_sales_invoices' : related_sales_invoices,
                      'azure_url' : analyzepdf.azure_url,
                      'start_pageno' : analyzepdf.start_pageno,
                      'status' : analyzepdf.status,
                      'file_name' : analyzepdf.file_name,
                      'created_at' : (analyzepdf.created_at) ? moment(analyzepdf.created_at).format('DD-MM-YYYY hh:mm A') : '-',
                      'updated_at' : (analyzepdf.updated_at) ? moment(analyzepdf.updated_at).format('DD-MM-YYYY hh:mm A') : '-',
                      'extracted_data' : analyzepdf.extracted_data,
                      'error' : analyzepdf.error,
                      'is_deleted' : analyzepdf.is_deleted,
                      'sync_status' : analyzepdf.sync_status
                    });
                    analyzepdf_completed_start = analyzepdf_completed_start + 1;
                    //});
                  }
                } //completed
                else if(!analyzepdf.is_deleted && (analyzepdf.status === 'processing' || analyzepdf.status === 'queued'))
                {  
                  //console.log("COM - processing");
                  //console.log(analyzepdf);
                  analyzepdf_processing_datas.push({
                    'id' : analyzepdf.id,
                    'fake_id' : analyzepdf_processing_start,
                    'invoice_type_name' : invoice_type_name,
                    'client_no' : org_no,
                    'client_name' : (client_name) ? client_name.toUpperCase() : null,
                    'invoice_type' : analyzepdf.invoice_type,                 
                    'invoice_no' : invoice_no,
                    'invoice_date' : invoice_date,
                    'currency' : currency,                  
                    'net_amount' : net_amount,
                    'exchange_currency' : exchange_currency,
                    'exchange_net_amount' : exchange_net_amount,                  
                    'related_sales_invoices' : related_sales_invoices,
                    'azure_url' : analyzepdf.azure_url,
                    'start_pageno' : analyzepdf.start_pageno,
                    'status' : analyzepdf.status,
                    'file_name' : analyzepdf.file_name,
                    'created_at' : (analyzepdf.created_at) ? moment(analyzepdf.created_at).format('DD-MM-YYYY hh:mm A') : '-',
                    'updated_at' : (analyzepdf.updated_at) ? moment(analyzepdf.updated_at).format('DD-MM-YYYY hh:mm A') : '-',
                    'extracted_data' : analyzepdf.extracted_data,
                    'error' : analyzepdf.error,
                    'is_deleted' : analyzepdf.is_deleted
                  });
                  analyzepdf_processing_start = analyzepdf_processing_start + 1; 
                } //processing
                else if(!analyzepdf.is_deleted && analyzepdf.status === 'failed')
                {  
                  //console.log("COM - failed");
                  analyzepdf_error_datas.push({
                    'id' : analyzepdf.id,
                    'fake_id' : analyzepdf_error_start,
                    'invoice_type_name' : invoice_type_name,
                    'client_no' : org_no,
                    'client_name' : (client_name) ? client_name.toUpperCase() : null,
                    'invoice_type' : analyzepdf.invoice_type,                 
                    'invoice_no' : invoice_no,
                    'invoice_date' : invoice_date,
                    'currency' : currency,                  
                    'net_amount' : net_amount, 
                    'exchange_currency' : exchange_currency,
                    'exchange_net_amount' : exchange_net_amount,                 
                    'related_sales_invoices' : related_sales_invoices,
                    'azure_url' : analyzepdf.azure_url,
                    'start_pageno' : analyzepdf.start_pageno,
                    'status' : analyzepdf.status,
                    'file_name' : analyzepdf.file_name,
                    'created_at' : (analyzepdf.created_at) ? moment(analyzepdf.created_at).format('DD-MM-YYYY hh:mm A') : '-',
                    'updated_at' : (analyzepdf.updated_at) ? moment(analyzepdf.updated_at).format('DD-MM-YYYY hh:mm A') : '-',
                    'extracted_data' : analyzepdf.extracted_data,
                    'error' : analyzepdf.error,
                    'is_deleted' : analyzepdf.is_deleted
                  });
                  analyzepdf_error_start = analyzepdf_error_start + 1;   
                } //error
                
                if(analyzepdf.is_deleted || analyzepdf.status === 'duplicate')
                {
                  analyzepdf_deleted_datas.push({
                    'id' : analyzepdf.id,
                    'fake_id' : analyzepdf_deleted_start,
                    'invoice_type_name' : invoice_type_name,
                    'client_no' : org_no,
                    'client_name' : (client_name) ? client_name.toUpperCase() : null,
                    'invoice_type' : analyzepdf.invoice_type,                 
                    'invoice_no' : invoice_no,
                    'invoice_date' : invoice_date,
                    'currency' : currency,                  
                    'net_amount' : net_amount,
                    'exchange_currency' : exchange_currency,
                    'exchange_net_amount' : exchange_net_amount,                  
                    'related_sales_invoices' : related_sales_invoices,
                    'azure_url' : analyzepdf.azure_url,
                    'start_pageno' : analyzepdf.start_pageno,
                    'status' : analyzepdf.status,
                    'file_name' : analyzepdf.file_name,
                    'created_at' : (analyzepdf.created_at) ? moment(analyzepdf.created_at).format('DD-MM-YYYY hh:mm A') : '-',
                    'updated_at' : (analyzepdf.updated_at) ? moment(analyzepdf.updated_at).format('DD-MM-YYYY hh:mm A') : '-',
                    'extracted_data' : analyzepdf.extracted_data,
                    'error' : analyzepdf.error,
                    'is_deleted' : analyzepdf.is_deleted,                    
                    'deleted_reason' : analyzepdf.deleted_reason,
                    'duplicate_message' : analyzepdf.duplicate_message
                  });
                  analyzepdf_deleted_start = analyzepdf_deleted_start + 1;
                } //deleted
              } //capture
              else if(type == 'analyzepdf_search')
              {
                if(!analyzepdf.is_deleted && analyzepdf.sync_status == 1)
                {
                  analyzepdf_commercial_invoice_datas.push({                 
                    'id' : analyzepdf.id,
                    'fake_id' : analyzepdf_commercial_invoice_start,
                    'client_no' : org_no,
                    'client_name' : (client_name) ? client_name.toUpperCase() : null,
                    'invoice_type' : analyzepdf.invoice_type,                 
                    'invoice_no' : invoice_no,
                    'invoice_date' : invoice_date,
                    'currency' : currency,                  
                    'net_amount' : net_amount, 
                    'exchange_currency' : exchange_currency,
                    'exchange_net_amount' : exchange_net_amount,                 
                    'related_sales_invoices' : related_sales_invoices,
                    'azure_url' : analyzepdf.azure_url,
                    'created_at' : (analyzepdf.created_at) ? moment(analyzepdf.created_at).format('DD-MM-YYYY hh:mm A') : '-',
                    'updated_at' : (analyzepdf.updated_at) ? moment(analyzepdf.updated_at).format('DD-MM-YYYY hh:mm A') : '-',
                    'error' : analyzepdf.error,
                    'is_deleted' : analyzepdf.is_deleted,
                    'extracted_data' : analyzepdf.extracted_data,                   
                  });
                  analyzepdf_commercial_invoice_start = analyzepdf_commercial_invoice_start + 1;
                }
              } //search
            } //commercial
          }//has data
          else
          {
            if(type == 'analyzepdf')
            {
              if(!analyzepdf.is_deleted && (analyzepdf.status === 'processing' || analyzepdf.status === 'queued'))
              {  
                //console.log("COM - processing");
                //console.log(analyzepdf);
                analyzepdf_processing_datas.push({
                  'id' : analyzepdf.id,
                  'fake_id' : analyzepdf_processing_start,
                  'invoice_type_name' : invoice_type_name,
                  'client_no' : org_no,
                  'client_name' : (client_name) ? client_name.toUpperCase() : null,
                  'invoice_type' : analyzepdf.invoice_type,                 
                  'invoice_no' : invoice_no,
                  'invoice_date' : invoice_date,
                  'currency' : currency,                  
                  'net_amount' : net_amount,
                  'exchange_currency' : exchange_currency,
                  'exchange_net_amount' : exchange_net_amount,                  
                  'related_sales_invoices' : related_sales_invoices,
                  'azure_url' : analyzepdf.azure_url,
                  'start_pageno' : analyzepdf.start_pageno,
                  'status' : analyzepdf.status,
                  'file_name' : analyzepdf.file_name,
                  'created_at' : (analyzepdf.created_at) ? moment(analyzepdf.created_at).format('DD-MM-YYYY hh:mm A') : '-',
                  'updated_at' : (analyzepdf.updated_at) ? moment(analyzepdf.updated_at).format('DD-MM-YYYY hh:mm A') : '-',
                  'extracted_data' : analyzepdf.extracted_data,
                  'error' : analyzepdf.error,
                  'is_deleted' : analyzepdf.is_deleted
                });
                analyzepdf_processing_start = analyzepdf_processing_start + 1; 
              } //processing
              else if(!analyzepdf.is_deleted && analyzepdf.status === 'failed')
              {  
                //console.log("COM - failed");
                analyzepdf_error_datas.push({
                  'id' : analyzepdf.id,
                  'fake_id' : analyzepdf_error_start,
                  'invoice_type_name' : invoice_type_name,
                  'client_no' : org_no,
                  'client_name' : (client_name) ? client_name.toUpperCase() : null,
                  'invoice_type' : analyzepdf.invoice_type,                 
                  'invoice_no' : invoice_no,
                  'invoice_date' : invoice_date,
                  'currency' : currency,                  
                  'net_amount' : net_amount,
                  'exchange_currency' : exchange_currency,
                  'exchange_net_amount' : exchange_net_amount,                  
                  'related_sales_invoices' : related_sales_invoices,
                  'azure_url' : analyzepdf.azure_url,
                  'start_pageno' : analyzepdf.start_pageno,
                  'status' : analyzepdf.status,
                  'file_name' : analyzepdf.file_name,
                  'created_at' : (analyzepdf.created_at) ? moment(analyzepdf.created_at).format('DD-MM-YYYY hh:mm A') : '-',
                  'updated_at' : (analyzepdf.updated_at) ? moment(analyzepdf.updated_at).format('DD-MM-YYYY hh:mm A') : '-',
                  'extracted_data' : analyzepdf.extracted_data,
                  'error' : analyzepdf.error,
                  'is_deleted' : analyzepdf.is_deleted
                });
                analyzepdf_error_start = analyzepdf_error_start + 1;   
              } //error

              if(analyzepdf.is_deleted || analyzepdf.status === 'duplicate')
              {
                analyzepdf_deleted_datas.push({
                  'id' : analyzepdf.id,
                  'fake_id' : analyzepdf_deleted_start,
                  'invoice_type_name' : invoice_type_name,
                  'client_no' : org_no,
                  'client_name' : (client_name) ? client_name.toUpperCase() : null,
                  'invoice_type' : analyzepdf.invoice_type,                 
                  'invoice_no' : invoice_no,
                  'invoice_date' : invoice_date,
                  'currency' : currency,                  
                  'net_amount' : net_amount,
                  'exchange_currency' : exchange_currency,
                  'exchange_net_amount' : exchange_net_amount,                  
                  'related_sales_invoices' : related_sales_invoices,
                  'azure_url' : analyzepdf.azure_url,
                  'start_pageno' : analyzepdf.start_pageno,
                  'status' : analyzepdf.status,
                  'file_name' : analyzepdf.file_name,
                  'created_at' : (analyzepdf.created_at) ? moment(analyzepdf.created_at).format('DD-MM-YYYY hh:mm A') : '-',
                  'updated_at' : (analyzepdf.updated_at) ? moment(analyzepdf.updated_at).format('DD-MM-YYYY hh:mm A') : '-',
                  'extracted_data' : analyzepdf.extracted_data,
                  'error' : analyzepdf.error,
                  'is_deleted' : analyzepdf.is_deleted,                    
                  'deleted_reason' : analyzepdf.deleted_reason,
                  'duplicate_message' : analyzepdf.duplicate_message
                });
                analyzepdf_deleted_start = analyzepdf_deleted_start + 1;
              } //deleted
            } //no data
          } //processing
        });

        if(type == 'analyzepdf')
        {
          // console.log(analyzepdf_completed_datas);
          // console.log(analyzepdf_processing_datas);
          // console.log(analyzepdf_error_datas);
          // console.log(analyzepdf_deleted_datas);

          return {
            'analyzepdf_completed_datas' : analyzepdf_completed_datas, 
            'analyzepdf_processing_datas' : analyzepdf_processing_datas,
            'analyzepdf_error_datas' : analyzepdf_error_datas,
            'analyzepdf_deleted_datas' : analyzepdf_deleted_datas
          }; 
        } //capture
        else if(type == 'analyzepdf_search')
        {          
          return {
            'analyzepdf_commercial_invoice_datas' : analyzepdf_commercial_invoice_datas, 
            'analyzepdf_sales_invoice_datas' : analyzepdf_sales_invoice_datas,
            'analyzepdf_declaration_datas' : analyzepdf_declaration_datas
          };
        } //search
        else
          return;
      }//analyzepdf_search 
      else if(type == 'crm_lead')
      {
        var leads = result['leads'];
        
        crm_lead_datas = [];
        var crm_lead_start = 1;
        $.each(leads, function (idx, lead) {
          crm_lead_datas.push({                             
            'id' : lead.id,
            'fake_id' : crm_lead_start,
            'cvr_number' : lead.cvr_number,
            'company_name' : lead.company_name,
            //'company_address' : lead.company_address,
            //'company_postcode' : lead.company_postcode,
            //'company_city' : lead.company_city,
            //'company_country' : lead.company_country,
            //'company_telephone' : lead.company_telephone,
            //'company_email' : lead.company_email,
            'company_website' : lead.company_website,
            //'company_desc' : lead.company_desc,
            //'company_employees' : lead.company_employees,
            //'financial_year' : lead.financial_year,
            //'revenue' : lead.revenue,
            //'rating' : lead.rating,
            //'potential_countries' : lead.potential_countries,
            //'potential_products' : lead.potential_products,
            'lead_date' : moment(lead.lead_date).format('DD-MM-YYYY'),
            'status' : lead.status,
            //'role' : lead.contact.role,
            'first_name' : lead.contact.first_name,
            'last_name' : lead.contact.last_name,
            'email' : lead.contact.email,
            'phone' : lead.contact.phone,
            'designation' : lead.contact.designation,
            'lang' : lead.contact.lang,
            'has_quote': (lead.quotes.length == 0) ? false : true
          });
          crm_lead_start = crm_lead_start + 1;
        });
       
        return crm_lead_datas;
      }//crm_lead 
      else if(type == 'crm_quote')
      {
        var quotes = result['quotes'];
        
        crm_active_quote_datas = [];   
        crm_negotiate_quote_datas = [];
        crm_approved_quote_datas = [];
        crm_rejected_quote_datas = [];

        var crm_active_quote_start = 1;
        var crm_negotiate_quote_start = 1;
        var crm_approved_quote_start = 1;
        var crm_rejected_quote_start = 1;

        $.each(quotes, function (idx, quote) {

          let addons = [];
          $.each(quote.addons, function (addonidx, addon) {
            if(addon.enabled)
            {
              addons.push({                             
                'id' : addon.id,
                'quote_id' : addon.quote_id,
                'name' : addon.addon_name,
                'price' : addon.price
              });
            }
          });

          if(quote.status == 'active')
          {
            crm_active_quote_datas.push({                             
              'id' : quote.id,
              'fake_id' : crm_active_quote_start,
              'lead_id' : quote.lead_id,
              'cvr_number' : quote.lead.cvr_number,
              'company_name' : quote.lead.company_name,                        
              'role' : quote.lead.contact.role,
              'first_name' : quote.lead.contact.first_name,
              'last_name' : quote.lead.contact.last_name,
              'email' : quote.lead.contact.email,
              'phone' : quote.lead.contact.phone,
              'designation' : quote.lead.contact.designation,
              'lang' : quote.lead.contact.lang,            
              'version': quote.version,
              'parent_quote_id': quote.parent_quote_id,
              'root_quote_id': quote.root_quote_id,
              'package': quote.package,
              'base_price': quote.base_price,
              'registration_price': quote.registration_price,
              'addons': addons,
              'status' : quote.status,
              'created_at' : moment(quote.created_at).format('DD-MM-YYYY hh:mm A')
            });
            crm_active_quote_start = crm_active_quote_start + 1;
          }
          else if(quote.status == 'negotiation')
          {
            crm_negotiate_quote_datas.push({                             
              'id' : quote.id,
              'fake_id' : crm_negotiate_quote_start,
              'lead_id' : quote.lead_id,
              'cvr_number' : quote.lead.cvr_number,
              'company_name' : quote.lead.company_name,                        
              'role' : quote.lead.contact.role,
              'first_name' : quote.lead.contact.first_name,
              'last_name' : quote.lead.contact.last_name,
              'email' : quote.lead.contact.email,
              'phone' : quote.lead.contact.phone,
              'designation' : quote.lead.contact.designation,
              'lang' : quote.lead.contact.lang,            
              'version': quote.version,
              'parent_quote_id': quote.parent_quote_id,
              'root_quote_id': quote.root_quote_id,
              'package': quote.package,
              'base_price': quote.base_price,
              'registration_price': quote.registration_price,
              'addons': addons,
              'status' : quote.status,
              'created_at' : moment(quote.created_at).format('DD-MM-YYYY hh:mm A')
            });
            crm_negotiate_quote_start = crm_negotiate_quote_start + 1;
          }
          else if(quote.status == 'approved')
          {
            crm_approved_quote_datas.push({                             
              'id' : quote.id,
              'fake_id' : crm_approved_quote_start,
              'lead_id' : quote.lead_id,
              'cvr_number' : quote.lead.cvr_number,
              'company_name' : quote.lead.company_name,                        
              'role' : quote.lead.contact.role,
              'first_name' : quote.lead.contact.first_name,
              'last_name' : quote.lead.contact.last_name,
              'email' : quote.lead.contact.email,
              'phone' : quote.lead.contact.phone,
              'designation' : quote.lead.contact.designation,
              'lang' : quote.lead.contact.lang,            
              'version': quote.version,
              'parent_quote_id': quote.parent_quote_id,
              'root_quote_id': quote.root_quote_id,
              'package': quote.package,
              'base_price': quote.base_price,
              'registration_price': quote.registration_price,
              'addons': addons,
              'status' : quote.status,
              'created_at' : moment(quote.created_at).format('DD-MM-YYYY hh:mm A')
            });
            crm_approved_quote_start = crm_approved_quote_start + 1;
          }
          else if(quote.status == 'rejected')
          {
            crm_rejected_quote_datas.push({                             
              'id' : quote.id,
              'fake_id' : crm_rejected_quote_start,
              'lead_id' : quote.lead_id,
              'cvr_number' : quote.lead.cvr_number,
              'company_name' : quote.lead.company_name,                        
              'role' : quote.lead.contact.role,
              'first_name' : quote.lead.contact.first_name,
              'last_name' : quote.lead.contact.last_name,
              'email' : quote.lead.contact.email,
              'phone' : quote.lead.contact.phone,
              'designation' : quote.lead.contact.designation,
              'lang' : quote.lead.contact.lang,            
              'version': quote.version,
              'parent_quote_id': quote.parent_quote_id,
              'root_quote_id': quote.root_quote_id,
              'package': quote.package,
              'base_price': quote.base_price,
              'registration_price': quote.registration_price,
              'addons': addons,
              'status' : quote.status,
              'created_at' : moment(quote.created_at).format('DD-MM-YYYY hh:mm A')
            });
            crm_rejected_quote_start = crm_rejected_quote_start + 1;
          }
        });
            
        let finalResult = [];

        /**
         * group by lead first
         */
        let byLead = {};

        crm_negotiate_quote_datas.forEach(item => {

            /**
             * STEP 1:
             * default collapse states
             */
            item.is_hidden = true;
            item.is_latest = false;

            if (!byLead[item.lead_id]) {
                byLead[item.lead_id] = [];
            }

            byLead[item.lead_id].push(item);
        });


        /**
         * version sort
         * supports:
         * 1
         * 1.1
         * 1.1.1
         * 1.2
         * etc
         */
        function sortVersion(a, b)
        {
            return a.version.localeCompare(
                b.version,
                undefined,
                { numeric: true }
            );
        }


        /**
         * build tree per lead
         */
        function buildTree(list)
        {
            let childrenMap = {};

            /**
             * build parent-child map
             */
            list.forEach(item => {

                let parentId = item.parent_quote_id ?? null;

                if (!childrenMap[parentId]) {
                    childrenMap[parentId] = [];
                }

                childrenMap[parentId].push(item);
            });

            let output = [];

            /**
             * recursive builder
             */
            function build(parentId, level)
            {
                let children = childrenMap[parentId] || [];

                children.sort(sortVersion);

                children.forEach(child => {

                    child.level = level;

                    output.push(child);

                    build(child.id, level + 1);
                });
            }

            /**
             * root nodes
             */
            let roots = childrenMap[null] || [];

            roots.sort(sortVersion);

            roots.forEach(root => {

                root.level = 0;

                output.push(root);

                build(root.id, 1);
            });

            /**
             * STEP 2:
             * show ONLY latest version initially
             */
            if(output.length > 0)
            {
                let latest = [...output]
                    .sort(sortVersion)
                    .pop();

                if(latest)
                {
                    latest.is_latest = true;
                    latest.is_hidden = false;
                }
            }

            return output;
        }


        /**
         * build per lead separately
         */
        Object.keys(byLead).forEach(leadId => {

            let tree = buildTree(byLead[leadId]);

            finalResult = finalResult.concat(tree);
        });


        crm_negotiate_quote_datas = finalResult;

        // let finalResult = [];

        // /**
        //  * group by lead first (IMPORTANT)
        //  */
        // let byLead = {};

        // crm_negotiate_quote_datas.forEach(item => {

        //     if (!byLead[item.lead_id]) {
        //         byLead[item.lead_id] = [];
        //     }

        //     byLead[item.lead_id].push(item);
        // });


        // /**
        //  * version sort (safe numeric dot sort)
        //  */
        // function sortVersion(a, b)
        // {
        //     return a.version.localeCompare(b.version, undefined, { numeric: true });
        // }


        // /**
        //  * build tree per lead
        //  */
        // function buildTree(list)
        // {
        //     let map = {};
        //     let childrenMap = {};

        //     list.forEach(item => {

        //         map[item.id] = item;

        //         let parentId = item.parent_quote_id;

        //         if (!childrenMap[parentId]) {
        //             childrenMap[parentId] = [];
        //         }

        //         childrenMap[parentId].push(item);
        //     });

        //     let output = [];

        //     function build(parentId, level)
        //     {
        //         let children = childrenMap[parentId] || [];

        //         children.sort(sortVersion);

        //         children.forEach(child => {

        //             child.level = level;

        //             output.push(child);

        //             build(child.id, level + 1);
        //         });
        //     }

        //     /**
        //      * root nodes = parent_quote_id = null
        //      */
        //     let roots = childrenMap[null] || [];

        //     roots.sort(sortVersion);

        //     roots.forEach(root => {

        //         root.level = 0;

        //         output.push(root);

        //         build(root.id, 1);
        //     });

        //     return output;
        // }


        // /**
        //  * build per lead separately
        //  */
        // Object.keys(byLead).forEach(leadId => {

        //     let tree = buildTree(byLead[leadId]);

        //     finalResult = finalResult.concat(tree);
        // });


        // crm_negotiate_quote_datas = finalResult;

       
        return {
          'crm_active_quote_datas' : crm_active_quote_datas, 
          'crm_negotiate_quote_datas' : crm_negotiate_quote_datas,
          'crm_approved_quote_datas' : crm_approved_quote_datas,
          'crm_rejected_quote_datas' : crm_rejected_quote_datas
        }; 
      }//crm_quote
      else if(type == 'crm_reminder')
      {
        var reminders = result['reminders'];
        
        crm_reminder_datas = [];
        var crm_reminder_start = 1;
        $.each(reminders, function (idx, reminder) {
          var lead = [];
          var quote = [];
          if(reminder.lead)
          {
            lead = reminder.lead;
          }
          else if(reminder.quote)
          {
            quote = reminder.quote;
            lead = reminder.quote.lead;
          }

          crm_reminder_datas.push({                             
            'id' : reminder.id,
            'fake_id' : crm_reminder_start,
            'sent_to' : reminder.sent_to,
            'reminder_date' : moment(reminder.reminder_date).format('DD-MM-YYYY'),
            'reminder_time' : reminder.reminder_time,
            'reminder_notes' : reminder.notes,
            'email_sent' : (reminder.email_sent) ? 1 : 0,

            'cvr_number' : lead.cvr_number,
            'company_name' : lead.company_name,
            //'company_address' : lead.company_address,
            //'company_postcode' : lead.company_postcode,
            //'company_city' : lead.company_city,
            //'company_country' : lead.company_country,
            //'company_telephone' : lead.company_telephone,
            //'company_email' : lead.company_email,
            'company_website' : lead.company_website,
            //'company_desc' : lead.company_desc,
            //'company_employees' : lead.company_employees,
            //'financial_year' : lead.financial_year,
            //'revenue' : lead.revenue,
            //'rating' : lead.rating,
            //'potential_countries' : lead.potential_countries,
            //'potential_products' : lead.potential_products,
            'lead_date' : moment(lead.lead_date).format('DD-MM-YYYY'),
            'lead_status' : lead.status,
            //'role' : lead.contact.role,
            'first_name' : lead.contact.first_name,
            'last_name' : lead.contact.last_name,
            'email' : lead.contact.email,
            'phone' : lead.contact.phone,
            'designation' : lead.contact.designation,
            'lang' : lead.contact.lang,
            
            'version': (quote) ? quote.version : null,
            'parent_quote_id': (quote) ? quote.parent_quote_id : null,
            'package': (quote) ? quote.package : null,
            'base_price': (quote) ? quote.base_price : null,
            'registration_price': (quote) ? quote.registration_price : null,
            'quote_status' : (quote) ? quote.status : null,

            'created_at' : moment(reminder.created_at).format('DD-MM-YYYY hh:mm A')
          });
          crm_reminder_start = crm_reminder_start + 1;
        });
       
        return crm_reminder_datas;
      }//crm_reminder 
    }

    window.customFilter = function customFilter(search_type, search_percentage, search_currency, invoice_type, invoice_vatpercentage, invoice_currency)
    {   
      var pushdata = false;
      if(search_type != '' && search_percentage != '' && search_currency != '')
      {    
        if(invoice_type == search_type  && Number(invoice_vatpercentage) == Number(search_percentage) && invoice_currency == search_currency)
          pushdata = true;
      }
      else
        pushdata = true;

      return pushdata;
    }

    window.toggleDtTableColumn = function toggleDtTableColumn(dt_names, elementName)
    {     
      var data = elementName.data();
      let columnIdx = data['column'];

      $.each(dt_names, function (idx, dt_name) {
        if(columnIdx == -1)   
        {
          dt_name.columns().eq(0).each( function (index) {
            if(index > 2)
            {
              let column = dt_name.column(index);
            
              var id = elementName.attr("id"); 
              if ($("#" + elementName.attr("id")).is(":checked")) 
                column.visible(true);
              else
                column.visible(false);  
            }
          });
        }
        else
        {
          let column = dt_name.column(columnIdx);
         
          var id = elementName.attr("id"); 
          if ($("#" + elementName.attr("id")).is(":checked")) 
            column.visible(true);
          else
            column.visible(false);  
        }
      });
    }   

    window.currencySymbol = function currencySymbol(locale, currency)
    {
      //const currencySymbol = (locale, currency) => {
        const formatter = new Intl.NumberFormat(locale, {
          style: 'currency',
          currency,
        });

        let symbol;
        formatter.formatToParts(0).forEach(({ type, value }) => {
          if (type === 'currency') {
            symbol = value;
          }
        });

        return symbol;
      //};
    }    

    window.isNumber = function isNumber(evt) {
        evt = (evt) ? evt : window.event;
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            return false;
        }
        return true;
    }

    window.parseAmountValue = function parseAmountValue(amount, currency_code = null)
    {
      let parsed_amount = 0;
             
      var currency_locale = 'en-US';
      if(currency_code == "DKK" || currency_code == "NOK")           
          currency_locale = 'da-DK';                  
      else if(currency_code == "SEK")       
          currency_locale = 'sv-SE';      
      else if(currency_code == "GBP")     
          currency_locale = 'en-GB';      
      else if(currency_code == "INR")        
          currency_locale = 'en-IN';
      else if(currency_code == "EUR")        
          currency_locale = 'fr-FR';
      else if(currency_code == "CHF")        
          currency_locale = 'fr-FR'; 

      if(currency_code == 'DKK' || currency_code == 'NOK')
      {        
        // Convert 00.000,00 format to a valid number
        let sanitizedValue = String(amount)
            .replace(/\−/g, '-')
            .replace(/\./g, '') // Remove thousand separators
            .replace(',', '.'); // Replace decimal comma with decimal point

        let parsedValue = parseFloat(sanitizedValue).toFixed(2);
        parsed_amount = isNaN(parsedValue) ? 0 : parseFloat(parsedValue); // Push the number or an empty string      
      }
      else if(currency_code == 'SEK' || currency_code == 'EUR' || currency_code == 'CHF')
      {        
        // Convert 00 000,00 format to a valid number
        let sanitizedValue = String(amount)
            .replace(/\−/g, '-')
            .replace(/\s/g, '') // Remove thousand separators
            .replace(',', '.'); // Replace decimal comma with decimal point

        let parsedValue = parseFloat(sanitizedValue).toFixed(2);
        parsed_amount = isNaN(parsedValue) ? 0 : parseFloat(parsedValue); // Push the number or an empty string      
      }
      else
      {  
        // Convert 00,000.00 format to a valid number
        let sanitizedValue = String(amount)
            .replace(/\−/g, '-')
            .replace(/\s/g, '') // Remove thousand separators
            .replace(/\,/g, ''); // Remove thousand separators

        let parsedValue = parseFloat(sanitizedValue).toFixed(2);  
        parsed_amount = isNaN(parsedValue) ? 0 : parseFloat(parsedValue); // Push the number or an empty string
      }

      return parsed_amount;
    }

    window.parseDenmarkFormat = function parseDenmarkFormat(amount)
    {
      //console.log(amount);
      //console.log(/,(\d{1,2})$/.test(amount));

      let formatted_amount = null;
      if (/,(\d{1,2})$/.test(amount))
      {                  
        let parse_amount = parseFloat(amount.replace(/\./g, '').replace(',', '.'));          
        formatted_amount = new Intl.NumberFormat('da-DK', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(parse_amount);  
      }
      else
      {
        if (/^\d+(\.\d+)?$/.test(amount))
        {
          if (amount.indexOf(',') > -1) 
          {          
            let parse_amount = parseAmountValue(amount, 'NOK');
            formatted_amount = new Intl.NumberFormat('da-DK', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(parse_amount);
          }
          else
          {
            let parse_amount = parseAmountValue(amount);
            formatted_amount = new Intl.NumberFormat('da-DK', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(parse_amount);
          }
        }
        else
        {
          let parse_amount = parseAmountValue(amount);
          formatted_amount = new Intl.NumberFormat('da-DK', {
              minimumFractionDigits: 2,
              maximumFractionDigits: 2
          }).format(parse_amount);
        }         
      }
      return formatted_amount;
    }

    window.normalizeAmountForFilter = function normalizeAmountForFilter(amount)
    {
      if (!amount || String(amount).trim() === '')
          return null;

      let sanitized = String(amount)
          .replace(/\−/g, '-')
          .replace(/\s/g, '')
          .replace(/\./g, '')
          .replace(',', '.')
          .replace(/\,/g, '');

      let parsed = parseFloat(sanitized);

      return isNaN(parsed) ? null : parsed;
    }

    window.parseVatRate = function parseVatRate(str) {
      if (!str) return null;

      // Remove spaces
      str = str.trim();

      // Remove all non-digit, non-dot, non-comma characters
      str = str.replace(/[^0-9.,]/g, '');

      if (!str) return null;

      // If both comma and dot exist, assume dot is decimal and comma is thousand separator
      if (str.indexOf('.') > -1 && str.indexOf(',') > -1) {
          str = str.replace(/,/g, ''); // remove commas
      }
      // If only comma exists, treat comma as decimal
      else if (str.indexOf(',') > -1) {
          str = str.replace(',', '.'); // convert comma to dot
      }

      let num = parseFloat(str);

      if (isNaN(num)) return null;

      // Remove trailing .0 if integer
      return Number.isInteger(num) ? num : num;
    }
});