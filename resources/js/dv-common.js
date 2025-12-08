/**
 * Common
 */

'use strict';

$(function () {      
  const periodmap = {
              "no_1": "january-february",
              "no_2": "march-april",
              "no_3": "may-june",
              "no_4": "july-august",
              "no_5": "september-october",
              "no_6": "november-december",
              "uk_1": "january-february-march",
              "uk_2": "february-march-april",
              "uk_3": "march-april-may",
              "uk_4": "april-may-june",
              "uk_5": "may-june-july",
              "uk_6": "june-july-august",
              "uk_7": "july-august-september",
              "uk_8": "august-september-october",
              "uk_9": "september-october-november",
              "uk_10": "october-november-december",
              "uk_11": "november-december-january",
              "uk_12": "december-january-february"
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
            'title' : reminder['title'], 
            'users' : reminder_users,
            //'client' : client['client_name'],          
            //'vatregmain' : vatregmain['country'] + " " + moment(vatregmain['service_start']).format('MMM Y') + " " + vatregmain['general_periods'],
            'client' : client_name,
            'vatregmain' : vat_country + " " + vat_start_date + " " + vat_general_periods,
            'reminder_action' : reminderactionoption['action_name'],
            'schedule' : reminder['schedule'],
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
              'title' : reminder['title'], 
              'users' : reminder_users,
              //'client' : client['client_name'],          
              //'vatregmain' : vatregmain['country'] + " " + moment(vatregmain['service_start']).format('MMM Y') + " " + vatregmain['general_periods'],
              'client' : client_name,            
              'vatregmain' : vat_country + " " + vat_start_date + " " + vat_general_periods,
              'reminder_action' : reminderactionoption['action_name'],
              'schedule' : reminder['schedule'],
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
                'email' : user['email']             
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
               || cominvoice['data_from'] == 'specific-global-search-refresh' || cominvoice['data_from'] == 'specific-invoice-global-search-refresh'
              || cominvoice['data_from'] == 'cron')  
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

                      var sales_net_amount = invoice['net_amount'];
                      var sales_shipping = invoice['shipping'];
                      var sales_variance = invoice['variance'];
                      var sales_vat_amount = invoice['vat_amount'];
                      var sales_currency = invoice['currency_code'];

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

                        var invoice_vat_percent = (invoice_vat_amount/(invoice_net_amount + invoice_shipping + invoice_variance)) * 100;

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

    window.parseAmountValue = function parseAmountValue(amount, currency_code)
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
});