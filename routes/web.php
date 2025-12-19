<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\testsample\TestSampleController;
//use App\Http\Controllers\Controller;
use App\Http\Controllers\chat\ChatTalkController;
//use App\Http\Controllers\client\ClientController;
use App\Http\Controllers\company\CompanyController;
use App\Http\Controllers\confirm\ConfirmController;
use App\Http\Controllers\dashboard\DashboardController;
use App\Http\Controllers\dvuser\DVUserController;
use App\Http\Controllers\email\EmailController;
use App\Http\Controllers\invoice\InvoiceController;
use App\Http\Controllers\language\LanguageController;
use App\Http\Controllers\reminder\ReminderController;
use App\Http\Controllers\system\StatsController;
use App\Http\Controllers\system\PaymentInfoController;
use App\Http\Controllers\system\ComplianceController;
use App\Http\Controllers\system\TaskDateController;
use App\Http\Controllers\system\ExcelColumnTemplateController;
use App\Http\Controllers\system\GlobalSearchController;
use App\Http\Controllers\tasks\TasksController;
use App\Http\Controllers\vat\VATRegistrationController;
use App\Http\Controllers\vatregmain\VATRegistrationMainController;
use App\Http\Controllers\RegisterController;

use App\Http\Controllers\declaration\DeclarationController;
use App\Http\Controllers\mailbox\MailboxController;

use App\Http\Controllers\previewreport\PreviewReportController;

use App\Http\Controllers\vatcheck\VATCheckController;

use App\Http\Controllers\anyexcel\AnyExcelController;

use App\Http\Controllers\RoleController;

use \App\Classes\DynamicsApiClass;
use \App\Classes\EconomicApiClass;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });
use App\Http\Controllers\PDFController;
Route::get('generate-pdf', [PDFController::class, 'generatePDF']);

/* -- WITH AUTH -- */
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified'
])->group(function () {   
   
    /* -- LOCALE -- */
    Route::get('lang/{locale}', [LanguageController::class, 'swap']);
    /* --end LOCALE -- */

    /* -- DASHBOARD -- */
        //Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('/', DashboardController::class);	

        /* -- For Testing Purpose -- */
        Route::get('coinvoices', [TestSampleController::class, 'coinvoices'])->name('coinvoices');  
        Route::get('exchangerateexcel', [TestSampleController::class, 'exchangerateexcel'])->name('exchangerateexcel');     
        //Route::get('sendreminderemail', [TestSampleController::class, 'sendreminderemail'])->name('sendreminderemail');

        Route::get('compliancesread', [TestSampleController::class, 'compliancesread'])->name('compliancesread'); 

        Route::get('excutescript', [TestSampleController::class, 'excutescript'])->name('excutescript'); 

        Route::get('checkvatnumbers', [TestSampleController::class, 'checkvatnumbers'])->name('checkvatnumbers'); 
        Route::get('checksinglevatnumber', [TestSampleController::class, 'checksinglevatnumber'])->name('checksinglevatnumber');
        Route::get('recheckvatnumbers', [TestSampleController::class, 'recheckvatnumbers'])->name('recheckvatnumbers'); 

        Route::get('emailforcompany', [TestSampleController::class, 'emailforcompany'])->name('emailforcompany'); 
        Route::get('reademailsrawphp', [TestSampleController::class, 'reademailsrawphp'])->name('reademailsrawphp'); 
        Route::get('reademails', [TestSampleController::class, 'reademails'])->name('reademails'); 

        Route::get('cvrapi', [TestSampleController::class, 'cvrapi'])->name('cvrapi'); 

        Route::get('generateinvoice', [TestSampleController::class, 'generateinvoice'])->name('generateinvoice'); 
        Route::get('readinvoice', [TestSampleController::class, 'readinvoice'])->name('readinvoice'); 

        Route::get('openai', [TestSampleController::class, 'openai'])->name('openai'); 
        Route::get('scanreceipt', [TestSampleController::class, 'scanreceipt'])->name('scanreceipt'); 

        Route::get('azuredb', [TestSampleController::class, 'azureDb'])->name('azure.db'); 
        Route::get('importrexml', [TestSampleController::class, 'importReXml'])->name('import.reconciliation.xml'); 

        Route::get('casddamonths', [TestSampleController::class, 'skippedCasDDaMonths'])->name('update.cca.dda.months'); 

        //Route::get('readcargodeclarationfile', [TestSampleController::class, 'readCargoDeclarationFile'])->name('read.cargo.declaration.file'); 

        Route::get('fetchimportreconciliationdatas', [TestSampleController::class, 'fetchImportReconciliationDatas'])->name('fetch.import.reconciliation.datas'); 
        Route::get('fetchinvoicenofromimportreconciliationfiles', [TestSampleController::class, 'fetchInvoiceNoFromImportReconciliationFiles'])->name('fetch.invoice.no.from.import.reconciliation.files'); 

        Route::get('efacto', [TestSampleController::class, 'efactoPayload'])->name('efacto'); 

        Route::get('readcargoemails', [TestSampleController::class, 'readCargoEmails'])->name('read.email.for.cargo.declaration.files'); 

        Route::get('fetchivfdatas', [TestSampleController::class, 'fetchIvfDatas'])->name('fetch.ivf.datas'); 

        Route::get('rematchcominvoice', [TestSampleController::class, 'rematchComInvoice'])->name('rematch.com.invoice'); 

        Route::get('readswissfiles', [TestSampleController::class, 'readSwissFiles'])->name('read.swiss.files'); 

        Route::get('disregardperiodfromspecificdate', [TestSampleController::class, 'disregardPeriodFromSpecificDate'])->name('disregard.period.from.specific.date'); 

        Route::get('missingsalesref', [TestSampleController::class, 'missingSalesInvoiceRefFiles'])->name('missing.sales.invoice.ref.files');

        Route::get('missingirfilesindb', [TestSampleController::class, 'missingIrFilesInDatabase'])->name('missing.ir.files.in.db');
        
        Route::get('convert-table-index', [TestSampleController::class, 'index'])->name('convert.table.index');
        Route::post('convert-table', [TestSampleController::class, 'convertHtmlTableToExcel'])->name('convert.table');

        Route::get('analyzepdf', [TestSampleController::class, 'analyzepdf'])->name('analyze.pdf.index');
        Route::post('analyze-pdf', [TestSampleController::class, 'analyze'])->name('analyze.pdf.post');

        /* -- STATISTICS -- */
        Route::get('stats-excel', [StatsController::class, 'exportToExcelStats'])->name('stats.export.excel'); 
        /* --end STATISTICS -- */
        /* --end For Testing Purpose -- */
    /* --end DASHBOARD -- */  
   
    /* -- ROLE: SUPER-ADMIN -- */
    Route::group(['middleware' => ['role:super-admin']], function () {        
        /* -- COMPANY -- */
            /* -- COMPANY:CREATE -- */
            Route::get('company/create', [CompanyController::class, 'createCompany'])->name('company.create');
            /* --end COMPANY:CREATE -- */

            /* -- COMPANY:STORE -- */
            Route::post('company', [CompanyController::class, 'storeCompany'])->name('company.store');
            /* --end COMPANY:STORE -- */ 

            /* -- COMPANY:EDIT -- */
            Route::get('company/{client_id}/edit', [CompanyController::class, 'editCompany'])->name('company.edit'); 
            /* --end COMPANY:EDIT -- */ 

            /* -- COMPANY:UPDATE -- */
            Route::put('company/{client_id}/updatestatus', [CompanyController::class, 'updateCompanyStatus'])->name('company.update.status');
            /* --end COMPANY:UPDATE -- */

            /* -- COMPANY:DELETE -- */
            Route::delete('company/{client_id}', [CompanyController::class, 'deleteCompany'])->name('company.delete');
            /* --end COMPANY:DELETE -- */
        /* --end COMPANY -- */

        /* -- SETTINGS -- */
            /* -- STATISTICS -- */
                /* -- STATISTICS:VIEW -- */
                Route::get('stats', [StatsController::class, 'index'])->name('stats.index'); 
                /* --end STATISTICS:VIEW -- */
            /* --end STATISTICS -- */
        /* --end SETTINGS -- */
    });
    /* -- ROLE: SUPER-ADMIN -- */

    /* -- ROLE: SUPER-ADMIN|COMPANY-ADMIN -- */
    Route::group(['middleware' => ['role:super-admin|company-admin']], function () {
        /* -- SETTINGS -- */            
            /* -- PAYMENT INFO -- */
                /* -- PAYMENT INFO:VIEW -- */
                Route::get('payment-info', [PaymentInfoController::class, 'index'])->name('payment.info.index');  
                /* --end PAYMENT INFO:VIEW -- */

                /* -- PAYMENT INFO:STORE -- */
                Route::post('payment-info', [PaymentInfoController::class, 'store'])->name('payment.info.store');
                /* --end PAYMENT INFO:STORE -- */
            /* --end PAYMENT INFO -- */  
            
            /* -- EMAIL TEMPLATE -- */
                /* -- EMAIL TEMPLATE:VIEW -- */
                Route::get('email-templates', [EmailController::class, 'index'])->name('email.templates'); 
                /* --end EMAIL TEMPLATE:VIEW -- */

                /* -- EMAIL TEMPLATE:PREVIEW -- */        
                Route::get('email-preview/{email_type}', [EmailController::class, 'show'])->name('email.preview'); 
                /* --end EMAIL TEMPLATE:PREVIEW -- */

                /* -- EMAIL TEMPLATE:UPDATE -- */    
                Route::put('email-template/{email_type}', [EmailController::class, 'update'])->name('email.template.update');
                /* --end EMAIL TEMPLATE:UPDATE -- */
            /* --end EMAIL TEMPLATE -- */

            /* -- TASK DATES -- */
                /* -- TASK DATES:VIEW -- */
                Route::get('taskdates', [TaskDateController::class, 'loadTaskDates'])->name('taskdates.list'); 
                /* --end TASK DATES:VIEW -- */

                /* -- TASK DATES:STORE -- */
                Route::post('taskdates', [TaskDateController::class, 'postTaskDate'])->name('taskdates.add'); 
                /* --end TASK DATES:STORE -- */

                /* -- TASK DATES:EDIT -- */
                Route::get('taskdates/{taskdate_id}/edit', [TaskDateController::class, 'editTaskdate'])->name('taskdates.edit');
                /* --end TASK DATES:EDIT -- */

                /* -- TASK DATES:DELETE -- */
                Route::delete('taskdates/{taskdate_id}', [TaskDateController::class, 'deleteTaskdate'])->name('taskdates.delete');
                /* --end TASK DATES:DELETE -- */
            /* --end TASK DATES -- */
        /* --end SETTINGS -- */

        /* -- COMPLIANCE -- */
            /* -- COMPLIANCE:VIEW -- */
            Route::get('compliance-user', [ComplianceController::class, 'complianceUser'])->name('compliance.user'); 
            /* --end COMPLIANCE:VIEW -- */

            /* -- COMPLIANCE:STORE -- */
            Route::post('compliance', [ComplianceController::class, 'readComplianceFile'])->name('compliance.file.read'); 
            /* --end COMPLIANCE:STORE -- */

            /* -- COMPLIANCE:GET SPLITTED FILES -- */
            Route::get('splitted-files', [ComplianceController::class, 'getSplittedFiles'])->name('compliance.get.splitted.files'); 
            /* --end COMPLIANCE:GET SPLITTED FILES -- */

            /* -- COMPLIANCE:DELETE SPLITTED FILES -- */
            Route::delete('splitted-files', [ComplianceController::class, 'deleteSplittedFiles'])->name('compliance.delete.splitted.files'); 
            /* --end COMPLIANCE:DELETE SPLITTED FILES -- */
        /* --end COMPLIANCE -- */

        /* -- REMINDER -- */
            /* -- REMINDER:VIEW -- */
            //Route::get('reminders', [ReminderController::class, 'loadReminders'])->name('reminder.list');
            Route::get('reminders/{reminder_type?}', [ReminderController::class, 'loadReminders'])->name('reminders'); 
            /* --end REMINDER:VIEW -- */

            /* -- REMINDER:REMINDER ACTIONS -- */
            Route::get('reminder/{user_role}/reminderactions', [ReminderController::class, 'loadReminderActions'])->name('reminder.action');
            /* --end REMINDER:REMINDER ACTIONS -- */

            /* -- REMINDER:COMPANIES -- */
            Route::get('reminder/{country}/companies', [ReminderController::class, 'loadReminderCompanies'])->name('reminder.company.list');
            /* --end REMINDER:COMPANIES -- */

            /* -- REMINDER:ALL COMPANIES -- */
            Route::get('reminder/{country}/allcompanies', [ReminderController::class, 'loadAllReminderCompanies'])->name('reminder.allcompany.list');
            /* --end REMINDER:ALL COMPANIES -- */

            /* -- REMINDER:USERS -- */
            //Route::get('reminder/{vat_reg_main_id}/users', [ReminderController::class, 'loadReminderUsers'])->name('reminder.user.list');
            //Route::get('reminder/users', [ReminderController::class, 'loadReminderUsers'])->name('reminder.user.list');
            Route::post('reminder/users', [ReminderController::class, 'loadReminderUsers'])->name('reminder.user.list');
            /* --end REMINDER:USERS -- */

            /* -- REMINDER:EDIT -- */
            Route::get('reminder/{reminder_id}/edit', [ReminderController::class, 'editReminder'])->name('reminder.edit');
            /* --end REMINDER:EDIT -- */

            /* -- REMINDER:STORE -- */
            Route::post('reminder', [ReminderController::class, 'postReminder'])->name('reminder.add'); 
            /* --end REMINDER:STORE -- */

            /* -- REMINDER:DELETE -- */
            Route::delete('reminder/{reminder_id}', [ReminderController::class, 'deleteReminder'])->name('reminder.delete');
            /* --end REMINDER:DELETE -- */

            /* -- REMINDER:HISTORY -- */
            Route::get('reminder-history', [ReminderController::class, 'historyReminder'])->name('reminder.history');
            /* --end REMINDER:HISTORY -- */

            /* -- REMINDER:SEND TEST EMAIL -- */
            Route::get('reminder/sendtestemail', [ReminderController::class, 'sendreminderemail'])->name('reminder.sendtestemail');
            /* --end REMINDER:SEND TEST EMAIL -- */
        /* --end REMINDER -- */
    });
    /* -- ROLE: SUPER-ADMIN|COMPANY-ADMIN -- */

    /* -- ROLE: SUPER-ADMIN|TEAM-USER|COMPANY-ADMIN -- */
    Route::group(['middleware' => ['role:super-admin|team-user|company-admin']], function () {
        /* -- CHAT TALK -- */
        Route::get('chattalk', [ChatTalkController::class, 'index'])->name('chat.talk');       
        Route::get('chattalk/{id}', [ChatTalkController::class, 'chatHistory'])->name('chat.talk.read'); 

        Route::group(['prefix'=>'ajax', 'as'=>'ajax::'], function() {
           Route::post('chattalk/send', [ChatTalkController::class, 'ajaxSendMessage'])->name('chat.talk.new');    
           Route::delete('chattalk/delete/{id}', [ChatTalkController::class, 'ajaxDeleteMessage'])->name('chat.talk.delete');    
        });
        /* --end CHAT TALK -- */  
       
        /* -- ALL TASKS -- */
        //Route::get('all-tasks', [TasksController::class, 'AllTasks'])->name('all.tasks'); 
        Route::get('all-tasks/more/{page}', [TasksController::class, 'AllTasksMore'])->name('all.tasks.more'); 
        /* --end ALL TASKS -- */        

        /* -- TASKS: UPLOADS -- */
        //Route::get('uploads', [TasksController::class, 'Uploads'])->name('uploads');      
        Route::get('uploads/{upload_file_type?}', [TasksController::class, 'Uploads'])->name('uploads');

           
        // Route::get('uploads/pivs', [TasksController::class, 'Uploads'])->name('uploads.pivs');         
        // Route::get('uploads/cas', [TasksController::class, 'Uploads'])->name('uploads.cas');         
        // Route::get('uploads/dda', [TasksController::class, 'Uploads'])->name('uploads.dda');         
        /* --end TASKS: UPLOADS -- */
        
        /* -- DV USERS -- */
            /* -- DV USERS:VIEW -- */
            Route::get('users', [DVUserController::class, 'loadUsers'])->name('users'); 
            /* --end DV USERS:VIEW -- */ 

            /* -- DV USERS:CREATE, STORE, EDIT, UPDATE, DELETE -- */
            Route::resource('dv-user', DVUserController::class);
            /* --end DV USERS:CREATE, STORE, EDIT, UPDATE, DELETE -- */
            
            /* -- DV USERS:TEAM USERS -- */  
            Route::get('team-user', [DVUserController::class, 'loadTeamUsers'])->name('team-user');
            /* --end DV USERS:TEAM USERS -- */

            /* -- DV USERS:ASSIGN TEAM USER TO VAT REGS. -- */
            Route::post('dv-user/assign', [DVUserController::class, 'assign'])->name('dv-user.assign');  
            /* --end DV USERS:ASSIGN TEAM USER TO VAT REGS. -- */

            /* -- DV USERS:ASSIGNED TEAM USER FOR VAT REGS. -- */       
            Route::get('dv-user/assigned/{dv_user_id}', [DVUserController::class, 'assigned'])->name('dv-user.assigned'); 
            /* --end DV USERS:ASSIGNED TEAM USER FOR VAT REGS. -- */ 

            /* -- DV USERS:ASSIGN COMPANIES TO TEAM USER -- */
            Route::post('company/assign', [DVUserController::class, 'assignCompany'])->name('dv-user.assign.company'); 
            /* --end DV USERS:ASSIGN COMPANIES TO TEAM USER -- */     

            /* -- DV USERS:ASSIGNED COMPANIES FOR TEAM USER -- */   
            Route::get('company/assigned/{dv_user_id}', [DVUserController::class, 'assignedCompany'])->name('dv-user.assigned.company');
            /* --end DV USERS:ASSIGNED COMPANIES FOR TEAM USER -- */

            /* -- DV USERS:COMPANY ADMINS -- */   
            Route::get('company-admin', [DVUserController::class, 'loadCompanyAdmin'])->name('company-admin'); 
            /* --end DV USERS:COMPANY ADMINS -- */   

            /* -- DV USERS:CLIENT USERS -- */
            Route::get('client-user', [DVUserController::class, 'loadClientUsers'])->name('client-user'); 
            /* --end DV USERS:CLIENT USERS -- */

            /* -- DV USERS:ASSIGN COMPANIES TO CLIENT USER -- */
            Route::post('client/assign', [DVUserController::class, 'assignClient'])->name('dv-user.assign.client');  
            /* --end DV USERS:ASSIGN COMPANIES TO CLIENT USER -- */

            /* -- DV USERS:ASSIGNED COMPANIES TO CLIENT USER -- */
            Route::get('client/assigned/{dv_user_id}', [DVUserController::class, 'assignedClient'])->name('dv-user.assigned.client'); 
            /* --end DV USERS:ASSIGNED COMPANIES TO CLIENT USER -- */

            /* -- DV USERS:NOTIFICATIONS -- */     
            Route::get('dv-user/notification/{user_id}', [DVUserController::class, 'loadNotification'])->name('dv-user.load.notification');
            /* --end DV USERS:NOTIFICATIONS -- */

            /* -- DV USERS:NOTIFICATION:STORE -- */
            Route::post('dv-user/notification/{user_id}', [DVUserController::class, 'postNotification'])->name('dv-user.post.notification');
            /* --end DV USERS:NOTIFICATION:STORE -- */

            /* -- DV USERS:INVOICE COLUMN SETTINGS -- */
            // Route::get('dv-user/invoice-column-settings/{user_id}', [DVUserController::class, 'loadInvoiceColumnSettings'])->name('dv-user.load.invoice.column.settings');
            /* --end DV USERS:INVOICE COLUMN SETTINGS -- */

            /* -- DV USERS:INVOICE COLUMN SETTINGS:STORE -- */
            Route::post('dv-user/invoice-column-settings/{user_id}', [DVUserController::class, 'postInvoiceColumnSettings'])->name('dv-user.post.invoice.column.settings');
            /* --end DV USERS:INVOICE COLUMN SETTINGS:STORE -- */
        /* --end DV USERS -- */              

        /* -- COMPANY -- */
            /* -- IMPORT RECONCILIATION TAB -- */
                /* -- PREVIEW REPORT:VIEW -- */       
                Route::get('preview-report/{vat_reg_id}', [PreviewReportController::class, 'index'])->name('preview.report');
                /* --end PREVIEW REPORT:VIEW -- */

                /* -- PREVIEW REPORT:EXPORT -- */
                Route::post('preview-report/{vat_reg_id}/export', [PreviewReportController::class, 'exportPdfPreviewReport'])->name('preview.report.pdf');
                /* --end PREVIEW REPORT:EXPORT -- */
            /* --end IMPORT RECONCILIATION TAB -- */    
        /* --end COMPANY -- */

        /* -- SETTINGS -- */
            /* -- GLOBAL SEARCH -- */
                /* -- GLOBAL SEARCH:VIEW -- */
                Route::get('global-search', [GlobalSearchController::class, 'index'])->name('global.search.index'); 
                /* --end GLOBAL SEARCH:VIEW -- */

                /* -- GLOBAL SEARCH:REFRESH -- */
                Route::get('global-search-refresh', [GlobalSearchController::class, 'refreshGlobalSearch'])->name('global.search.refresh');   
                /* --end GLOBAL SEARCH:REFRESH -- */  

                /* -- GLOBAL SEARCH:REFRESH STATUS -- */
                Route::get('global-search-refresh/batch-status/{batch_id}', [GlobalSearchController::class, 'refreshGlobalSearchStatus'])->name('global.search.refresh.status');   
                /* --end GLOBAL SEARCH:REFRESH STATUS -- */  
            /* --end GLOBAL SEARCH -- */

            /* -- MAILBOX -- */
            Route::get('mail-box-files', [MailboxController::class, 'index'])->name('mail.box.files.index'); 

            Route::post('mail-box-files/assign', [MailboxController::class, 'assign'])->name('mail.box.files.assign');   

            Route::delete('mail-box-files/dismiss', [MailboxController::class, 'dismiss'])->name('mail.box.files.dismiss');  
            /* --end MAILBOX -- */ 
        /* --end SETTINGS -- */       
    });
    /* --end ROLE: SUPER-ADMIN|TEAM-USER|COMPANY-ADMIN -- */

    /* -- ROLE: SUPER-ADMIN|CLIENT-USER -- */
    Route::group(['middleware' => ['role:super-admin|client-user']], function () {        
        /* -- COMPANY -- */            
            /* -- COMPANY:UPDATE -- */
            Route::put('company/{client_id}/updatestatus', [CompanyController::class, 'updateCompanyStatus'])->name('company.update.status');
            /* --end COMPANY:UPDATE -- */
        /* --end COMPANY -- */ 

        /* -- OTHERS -- */
        //Route::get('vat-accountnos/{client_id}', [VATRegistrationController::class, 'loadVATAccountNos'])->name('vat.account.nos'); 
        Route::get('accountnos/{client_id}', [VATRegistrationMainController::class, 'loadAccountNos'])->name('account.nos');
        Route::get('editaccountnos', [VATRegistrationMainController::class, 'loadEditAccountNos'])->name('account.nos.edit');

        Route::get('vatnos', [ComplianceController::class, 'loadVatNos'])->name('vat.nos');
        Route::get('cvr-vat-no/refreshcvr', [ComplianceController::class, 'loadCVRCompany'])->name('cvrvat.nos');
        /* --end OTHERS -- */   
    });
    /* --end ROLE: SUPER-ADMIN|CLIENT-USER -- */
            
    /* -- ROLE: SUPER-ADMIN|TEAM-USER|CLIENT-USER|COMPANY-ADMIN -- */
    Route::group(['middleware' => ['role:super-admin|team-user|client-user|company-admin']], function () {
        /* -- COMPANY -- */
            /* -- COMPANY:LIST -- */
            Route::get('companies', [CompanyController::class, 'loadCompanies'])->name('companies');
            /* --end COMPANY:LIST -- */ 

            /* -- COMPANY:VIEW -- */
            Route::get('company/{client_id}', [CompanyController::class, 'showCompany'])->name('company.show');
            /* --end COMPANY:VIEW -- */  

            /* -- COMPANY:CVR - ONLY FOR DK  -- */
            Route::get('cvr-details/{vat_no}', [CompanyController::class, 'getCVRDetails'])->name('cvr.details');
            /* --end COMPANY:CVR - ONLY FOR DK  -- */

            /* -- VAT RETURNS TAB -- */                
                Route::get('vat-returns-tab/{client_id}', [TasksController::class, 'loadVatReturnsTab'])->name('vat.returns.tab');
                
                /* -- OVERVIEW TAB -- */
                    /* -- OVERVIEW: INVOICES ROWS -- */
                    Route::get('vat-return-overview-tab/{vat_reg_id}', [TasksController::class, 'loadOverviewTabLazy'])->name('vat.return.overview.tab');
                    /* --end OVERVIEW: INVOICES ROWS -- */

                    /* -- OVERVIEW:RE-OPEN FOLDER -- */
                    Route::post('vat-return/comment/{vat_reg_id}', [VATRegistrationController::class, 'uploadCommentWithFilesToOneDrive'])->name('vat.return.upload.comment.files');
                    /* --end OVERVIEW:RE-OPEN FOLDER -- */

                    /* -- OVERVIEW:RE-OPEN FOLDER:SEND EMAIL -- */
                    Route::post('send-comment-email/{comment_id}', [VATRegistrationController::class, 'sendCommentEmailToClientUser'])->name('vat.return.comment.email');
                    /* -- OVERVIEW:RE-OPEN FOLDER:SEND EMAIL -- */

                    /* -- DISREGARD PERIOD -- */
                    Route::post('disregard-period/{vat_reg_id}', [VATRegistrationController::class, 'disregardPeriod'])->name('disregard.period');
                    /* --end DISREGARD PERIOD -- */

                    /* -- CANCEL PENDING REVIEW -- */
                    Route::get('cancel-pending-review/{vat_reg_id}',  [VATRegistrationController::class, 'cancelPendingReview'])->name('cancel.pending.review');
                    /* --end CANCEL PENDING REVIEW  -- */
                /* --end OVERVIEW TAB -- */

                /* -- INVOICES TAB -- */                
                    /* -- INVOICES:VIEW -- */       
                    Route::get('invoices/{vat_reg_id}', [InvoiceController::class, 'InvoiceController'])->name('invoices');
                    
                    Route::get('invoices/{vat_reg_id}/current', [InvoiceController::class, 'currentInvoices'])->name('invoices.current');
                    /* --end INVOICES:VIEW -- */          
                   
                    /* -- INVOICES:SAFT -- */   
                    Route::post('invoices/{vat_reg_id}/downloadsaft', [InvoiceController::class, 'show'])->name('invoice.downloadsaft');
                    /* --end INVOICES:SAFT -- */   

                    /* -- INVOICES:DOWNLOAD -- */   
                    Route::post('invoice/download/{vat_reg_id}', [InvoiceController::class, 'downloadInvoice'])->name('invoice.download');
                    /* --end INVOICES:DOWNLOAD -- */    

                    /* -- INVOICES:CONVERT CURRENCY -- */   
                    Route::post('invoices/{vat_reg_id}/convert', [InvoiceController::class, 'convertInvoiceCurrency'])->name('invoice.convert.currency');
                    /* --end INVOICES:CONVERT CURRENCY -- */ 

                    /* -- INVOICES: REFRESH/INSERT INVOICES -- */
                    Route::get('invoices/{vat_reg_id}/refresh', [InvoiceController::class, 'refreshInvoices'])->name('invoice.refresh');
                    /* --end INVOICES: REFRESH/INSERT INVOICES -- */

                    /* -- INVOICES: DISREGARD INVOICES -- */
                    Route::post('invoices/{invoice_id}/disregard', [InvoiceController::class, 'invoiceDisregard'])->name('invoice.disregard');
                    /* --end INVOICES: DISREGARD INVOICES -- */

                    /* -- INVOICES:INVOICE COLUMN SETTINGS:STORE -- */
                    Route::post('invoices/invoice-column-settings', [InvoiceController::class, 'postInvoiceColumnSettings'])->name('invoice.post.invoice.column.settings');
                    /* --end INVOICES:INVOICE COLUMN SETTINGS:STORE -- */
                /* --end INVOICES TAB -- */  

                /* -- DOCUMENTS TAB -- */
                    /* -- FILE UPLOAD -- */
                        /* -- FILE: VIEW -- */
                        Route::get('file/{vat_reg_id}', [VATRegistrationController::class, 'loadFile'])->name('file.load');
                        /* --end FILE: VIEW -- */

                        /* -- FILE: STORE -- */
                        Route::post('file/{vat_reg_id}', [VATRegistrationController::class, 'uploadFileToOneDrive'])->name('file.upload');
                        /* --end FILE: STORE -- */

                        /* -- FILE: DELETE -- */
                        Route::delete('file/{file_id}', [VATRegistrationController::class, 'deleteFileFromOneDrive'])->name('file.delete');
                        /* --end FILE: DELETE -- */

                        /* -- FILE: DOWNLOAD -- */
                        Route::get('file/{file_id}/download', [VATRegistrationController::class, 'downloadFileFromOneDrive'])->name('file.download');                        
                        /* --end FILE: DOWNLOAD -- */

                        /* -- FILE: REFRESH -- */
                        Route::get('file/{file_id}/refresh', [VATRegistrationController::class, 'refreshFileToLoadDatas'])->name('file.refresh');                        
                        /* --end FILE: REFRESH -- */

                        /* -- FILE: NUMBER UPDATE -- */
                        Route::put('file/{file_id}', [VATRegistrationController::class, 'updateFileNumber'])->name('file.update'); 
                        /* --end FILE: NUMBER UPDATE -- */

                        /* -- FILE:EXCEL/XML VIEW -- */                        
                        Route::get('vat-return/filelazy/{vat_reg_id}', [VATRegistrationController::class, 'loadVatReturnFileLazy'])->name('vat.return.file.lazy.load');
                        /* --end FILE:EXCEL/XML VIEW -- */

                        /* -- FILE:EXCEL/XML STORE -- */                        
                        Route::post('vat-return/filelazy/{vat_reg_id}', [VATRegistrationController::class, 'uploadVatReturnFileToOneDriveLazy'])->name('vat.return.file.lazy.upload'); 
                        /* --end FILE:EXCEL/XML STORE -- */

                        /* -- FILE:EXCEL/XML DELETE -- */                        
                        Route::delete('vat-return/filelazy/{file_id}', [VATRegistrationController::class, 'deleteVatReturnFileFromOneDriveLazy'])->name('vat.return.file.lazy.delete');  
                        /* --end FILE:EXCEL/XML DELETE -- */

                        /* -- FILE: DOWNLOAD -- */                        
                        Route::get('vat-return/filelazy/{file_id}/download', [VATRegistrationController::class, 'downloadVatReturnFileFromOneDriveLazy'])->name('vat.return.file.lazy.download');     
                        /* --end FILE: DOWNLOAD -- */
                    /* --end FILE UPLOAD -- */

                    /* -- RE-SEND EMAIL -- */
                    Route::post('file-email/{vat_reg_id}', [VATRegistrationController::class, 'sendEmailFileToClientUser'])->name('file.email');
                    /* --end RE-SEND EMAIL -- */

                    /* -- DISREGARD TASK -- */
                    Route::post('disregard-task/{vat_reg_id}', [VATRegistrationController::class, 'disregardTask'])->name('disregard.task');
                    /* --end DISREGARD TASK -- */ 

                    /* -- RECEIPT:VIEW -- */
                    Route::get('vat-return/receipt/{vat_reg_id}', [VATRegistrationController::class, 'loadFromOneDrive'])->name('vat.return.load.receipt');
                    /* --end RECEIPT:VIEW -- */

                    /* -- RECEIPT:STORE -- */
                    //Route::post('vat-return/receipt/{vat_reg_id}', [VATRegistrationController::class, 'uploadToOneDrive'])->name('vat.return.upload.receipt');
                    Route::post('vat-return/receipt/{vat_reg_id}', [VATRegistrationController::class, 'uploadReceipt'])->name('vat.return.upload.receipt');
                    /* --end RECEIPT:STORE -- */

                    /* -- RECEIPT:DELETE -- */
                    Route::delete('vat-return/receipt/{file_id}', [VATRegistrationController::class, 'deleteFromOneDrive'])->name('vat.return.delete.receipt');
                    /* --end RECEIPT:DELETE -- */ 

                    /* -- EXCEL COLUMN MAPPING TEMPLATE -- */                                            
                    Route::post('excel-column-mapping-template/{vat_reg_id}', [VATRegistrationController::class, 'excelColumnMappingTemplate'])->name('excel.column.mapping.template');              
                    /* --end EXCEL COLUMN MAPPING TEMPLATE -- */  

                    // /* -- EXCEL COLUMN TEMPLATES -- */                        
                    // Route::resource('excel-column-templates', ExcelColumnTemplateController::class);  
                    // Route::get('excel-column-templates/sheet/{sheet_no}', [ExcelColumnTemplateController::class, 'excelColumnTemplatesSheet'])->name('excel.column.templates.sheet');    
                    // Route::get('excel-column-templates/sheetnew/{sheet_no}', [ExcelColumnTemplateController::class, 'excelColumnTemplatesSheetNEW'])->name('excel.column.templates.sheet.NEW');
                    // Route::get('excel-column-templates/filenew/{file_no}', [ExcelColumnTemplateController::class, 'excelColumnTemplatesFileNEW'])->name('excel.column.templates.file.NEW');    
                    // Route::get('excel-column-templates/rownew/{row_no}', [ExcelColumnTemplateController::class, 'excelColumnTemplatesRowNEW'])->name('excel.column.templates.row.NEW');       
                    // /* --end EXCEL COLUMN TEMPLATES -- */ 

                    /* -- ANY EXCEL TEMPLATES -- */
                        Route::resource('anyexcel-template', AnyExcelController::class);

                        /* -- ANY EXCEL TEMPLATE FILE: STORE -- */
                        Route::post('anyexcel-template/upload', [AnyExcelController::class, 'upload'])->name('any.excel.template.file.upload');
                        /* --end ANY EXCEL TEMPLATEFILE: STORE -- */
                /* --end ANY EXCEL TEMPLATES -- */   
                /* --end DOCUMENTS TAB -- */

                /* -- IMPORT TAB -- */
                    /* -- IMPORT VAT:VIEW -- */
                    Route::get('vat-return-importvat-tab/{vat_reg_id}', [TasksController::class, 'loadImportVatTabLazy'])->name('vat.return.importvat.tab');
                    /* --end IMPORT VAT:VIEW -- */

                    /* -- IMPORT VAT:STORE -- */
                    Route::post('import-vat/{import_vat_id}', [VATRegistrationController::class, 'postImportVat'])->name('vat.return.import.vat');
                    /* --end IMPORT VAT:STORE -- */

                    /* -- IMPORT VAT COMMENT:STORE -- */
                    Route::post('import-vat-files/{import_vat_id}/comment/{import_vat_line_no}', [VATRegistrationController::class, 'postImportVatFileComment'])->name('import.vat.files.comment');
                    /* --end IMPORT VAT COMMENT:STORE -- */

                    /* -- IMPORT VAT COMMENT:DELETE -- */
                    Route::delete('import-vat-files/{import_vat_id}/comment', [VATRegistrationController::class, 'deleteImportVatFileComment'])->name('import.vat.files.comment.delete');
                    /* --end IMPORT VAT COMMENT:DELETE -- */

                    /* -- IMPORT VAT COMMENT:UPDATE SEND EMAIL STATUS -- */
                    Route::put('import-vat-files/{import_vat_id}/updatesendemail', [VATRegistrationController::class, 'updateSendEmail'])->name('client.update.sendemail'); 
                    /* --end IMPORT VAT COMMENT:UPDATE SEND EMAIL STATUS -- */ 
                /* --end IMPORT TAB -- */

                /* -- SUBMITTING FIELDS TAB -- */
                    Route::get('vat-return-submittingfields-tab/{vat_reg_id}', [TasksController::class, 'loadSubmittingFieldsTab'])->name('vat.return.submittingfields.tab');

                    /* -- SUBMITTING FIELDS:STORE:GB, NO, CH -- */
                    Route::post('submittingfields/{vat_reg_id}', [VATRegistrationController::class, 'postSubmittingFields'])->name('vat.return.submitting.fields');
                    /* --end SUBMITTING FIELDS:STORE:GB, NO, CH -- */

                    /* -- SUBMITTING FIELDS:STORE:NO -- */
                    // Route::post('submittingfieldsno/{vat_reg_id}', [VATRegistrationController::class, 'postSubmittingFieldsNO'])->name('vat.return.submitting.fields.NO');
                    /* --end SUBMITTING FIELDS:STORE:NO -- */

                    /* -- SUBMITTING FIELDS:EXPORT -- */
                    Route::post('submittingfields/{vat_reg_id}/export', [VATRegistrationController::class, 'exportToExcelSubmittingFields'])->name('submitting.fields.export.excel');
                    /* --end SUBMITTING FIELDS:EXPORT -- */
                /* --end SUBMITTING FIELDS TAB -- */

                /* -- HISTORY TAB -- */
                Route::get('vat-return-history-tab/{vat_reg_id}', [TasksController::class, 'loadHistoryTabLazy'])->name('vat.return.history.tab');
                /* --end HISTORY TAB -- */

                /* -- NOTES TAB -- */
                    Route::get('vat-return-notes-tab/{vat_reg_id}', [TasksController::class, 'loadVatReturnNotesTab'])->name('vat.return.notes.tab');
                    Route::post('vat-return-notes-tab/{vat_reg_id}', [TasksController::class, 'postVatReturnNotes'])->name('vat.return.notes.store');
                    Route::delete('vat-return-notes-tab/{note_id}', [TasksController::class, 'deleteVatReturnNotes'])->name('vat.return.notes.delete');
                /* --end NOTES TAB -- */

                /* -- VAT CONTROL TAB -- */
                    Route::get('vat-return-control-tab/{vat_reg_id}', [TasksController::class, 'loadVatReturnControlTab'])->name('vat.return.control.tab');
                /* --end VAT CONTROL TAB -- */
                    
                /* -- VAT CHECK TAB -- */                
                    /* -- VAT CHECK:VIEW -- */       
                    Route::get('vatcheck/{vat_reg_id}', [VATCheckController::class, 'index'])->name('vat.check');
                    /* --end VAT CHECK:VIEW -- */                                                                                     
                /* --end VAT CHECK TAB -- */                
            /* --end VAT RETURNS TAB -- */

            /* -- IMPORT RECONCILIATION TAB -- */
                Route::get('import-reconciliation-tab/{client_id}', [TasksController::class, 'loadImportReconciliationTab'])->name('import.reconciliation.tab');

                /* -- OVERVIEW TAB -- */
                    /* -- OVERVIEW: INVOICES ROWS -- */
                    Route::get('import-reconciliation-overview-tab/{vat_reg_id}', [TasksController::class, 'loadImportReconciliationOverviewTabLazy'])->name('import.reconciliation.overview.tab');
                    /* --end OVERVIEW: INVOICES ROWS -- */

                    /* -- OVERVIEW: INVOICES ROWS - SALES INVOICE VAT AMOUNT -- */
                    Route::get('import-reconciliation-overview-tab-sales-invoice-vat-amount/{vat_reg_id}', [TasksController::class, 'loadImportReconciliationOverviewTabSalesInvoiceVatAmount'])->name('import.reconciliation.overview.tab.sales.invoice.vat.amount');
                    /* --end OVERVIEW: INVOICES ROWS - SALES INVOICE VAT AMOUNT -- */
                /* --end OVERVIEW TAB -- */    

                // /* -- RECEIPT:STORE -- */                   
                // Route::post('vat-return/receipt/{vat_reg_id}', [VATRegistrationController::class, 'uploadReceipt'])->name('vat.return.upload.receipt');
                // /* --end RECEIPT:STORE -- */

                /* -- DECLARATIONS TAB -- */                
                    /* -- DECLARATIONS:VIEW -- */       
                        Route::get('declarations/{vat_reg_id}', [DeclarationController::class, 'index'])->name('declarations');

                        /* -- DECLARATIONS: DECLARATION/COM/SALES INVOICE - REFRESH GS -- */
                            Route::get('declaration-invoice/{vat_reg_id}/global-search-refresh', [DeclarationController::class, 'refreshGlobalSearch'])->name('declaration.invoice.refresh.global.search');    

                            /* -- DECLARATIONS:REFRESH GS STATUS -- */
                            Route::get('declaration-invoice/{vat_reg_id}/batch-status/{batch_id}', [DeclarationController::class, 'refreshGlobalSearchStatus'])->name('declaration.invoice.global.search.refresh.status');   
                            /* --end DECLARATIONS:REFRESH GS STATUS -- */                    
                        /* --end DECLARATIONS: DECLARATION/COM/SALES INVOICE - REFRESH GS -- */

                        /* -- DECLARATIONS: COM/SALES INVOICE - SPECIFIC REFRESH GS -- */
                        Route::post('declaration-invoice/{invoice_id}/refresh', [DeclarationController::class, 'refreshSpecificData'])->name('declaration.invoice.refresh.specific.global.search'); 
                        /* --end DECLARATIONS: COM/SALES INVOICE - SPECIFIC REFRESH GS -- */

                        /* -- DECLARATIONS: DECLARATION/COM/SALES INVOICE - ADD COMMENT/DISREGARD -- */
                        Route::post('declaration-invoice/{invoice_id}/disregard', [DeclarationController::class, 'invoiceDisregard'])->name('declaration.invoice.disregard');
                        /* --end DECLARATIONS: DECLARATION/COM/SALES INVOICE - ADD COMMENT/DISREGARD -- */

                        /* -- DECLARATIONS: DECLARATION/COM/SALES INVOICE - DELETE COMMENT -- */
                        Route::delete('declaration-invoice/{invoice_id}/deletecomment', [DeclarationController::class, 'invoiceDeleteComment'])->name('declaration.invoice.delete.comment');
                        /* --end DECLARATIONS: DECLARATION/COM/SALES INVOICE - DELETE COMMENT -- */

                        /* -- DECLARATIONS: COM INVOICE - DELETE -- */
                        Route::delete('declaration-invoice/{invoice_id}', [DeclarationController::class, 'invoiceDelete'])->name('declaration.invoice.delete');
                        /* --end DECLARATIONS: COM INVOICE - DELETE -- */

                        /* -- DECLARATIONS:SALES INVOICE-DISREGARD -- */
                        //Route::post('declaration-sales-invoice/{invoice_id}/disregard', [DeclarationController::class, 'salesInvoiceDisregard'])->name('declaration.sales.invoice.disregard');
                        /* --end DECLARATIONS:SALES INVOICE-DISREGARD -- */

                        /* -- DECLARATIONS:COM. INVOICE-DISREGARD -- */
                        //Route::get('declaration-com-invoice/{invoice_id}/disregard', [DeclarationController::class, 'comInvoiceDisregard'])->name('declaration.com.invoice.disregard');
                        /* --end DECLARATIONS:COM. INVOICE-DISREGARD -- */
                        
                        /* -- DECLARATIONS: COM INVOICE - REMATCH -- */
                        Route::post('declaration-invoice/{invoice_id}/rematch', [DeclarationController::class, 'invoiceRematch'])->name('declaration.invoice.rematch');
                        /* --end DECLARATIONS: COM INVOICE - REMATCH -- */

                        /* -- DECLARATIONS: COM INVOICE - REMOVE REMATCH -- */
                        Route::delete('declaration-invoice/{invoice_id}/rematch', [DeclarationController::class, 'invoiceRemoveRematch'])->name('declaration.invoice.remove.rematch');
                        /* --end DECLARATIONS: COM INVOICE - REMOVE REMATCH -- */

                        /* -- DECLARATIONS: SALES INVOICE - FTP - EDIT -- */
                        Route::get('declaration-invoice/{invoice_id}/edit', [DeclarationController::class, 'invoiceEdit'])->name('declaration.invoice.edit');

                        Route::post('declaration-invoice/{invoice_id}/edit', [DeclarationController::class, 'invoiceEditSave'])->name('declaration.invoice.edit.save');
                        /* --end DECLARATIONS: SALES INVOICE - FTP - EDIT -- */

                        /* -- DECLARATIONS: SALES INVOICE - MOVE -- */
                        Route::post('declaration-invoice/{invoice_id}/move', [DeclarationController::class, 'invoiceMove'])->name('declaration.sales.invoice.move');
                        /* --end DECLARATIONS: SALES INVOICE - MOVE -- */

                        /* -- DECLARATIONS: DECLARATION/COM/SALES INVOICE - CURRENCY CONVERSION -- */
                            Route::post('declaration-invoice/{vat_reg_id}/convert', [DeclarationController::class, 'convertInvoiceCurrency'])->name('declaration.invoice.convert'); 
                        /* --end DECLARATIONS: DECLARATION/COM/SALES INVOICE - CURRENCY CONVERSION -- */

                        /* -- DECLARATIONS: COM INVOICE - UNMATCH -- */
                        Route::post('declaration-invoice/{invoice_id}/unmatch', [DeclarationController::class, 'invoiceUnmatch'])->name('declaration.invoice.unmatch');
                        /* --end DECLARATIONS: COM INVOICE - UNMATCH -- */

                        /* -- DECLARATIONS: SALES INVOICE FILE - MOVE -- */
                        Route::post('declaration-invoice/{invoice_id}/move-file', [DeclarationController::class, 'invoiceFileMove'])->name('declaration.sales.invoice.file.move');
                        /* --end DECLARATIONS: SALES INVOICE FILE - MOVE -- */
                    /* --end DECLARATIONS:VIEW -- */ 

                    /* -- CARGO DECLARATION FILES -- */
                    Route::get('cargo-declaration-files/{import_vat_id}', [DeclarationController::class, 'cargoDeclarationFiles'])->name('cargo.declaration.files');  
                    /* --end CARGO DECLARATION FILES -- */                                                                   
                /* --end DECLARATIONS TAB -- */ 

                /* -- HISTORY TAB -- */
                Route::get('import-reconciliation-history-tab/{vat_reg_id}', [TasksController::class, 'loadImportReconciliationHistoryTab'])->name('import.reconciliation.history.tab');
                /* --end HISTORY TAB -- */

                /* -- NOTES TAB -- */
                    Route::get('import-reconciliation-notes-tab/{vat_reg_id}', [TasksController::class, 'loadImportReconciliationNotesTab'])->name('vat.return.notes.tab');
                    Route::post('import-reconciliation-notes-tab/{vat_reg_id}', [TasksController::class, 'postImportReconciliationNotes'])->name('vat.return.notes.store');
                    Route::delete('import-reconciliation-notes-tab/{note_id}', [TasksController::class, 'deleteImportReconciliationNotes'])->name('vat.return.notes.delete');
                /* --end NOTES TAB -- */ 

                /* -- CONTROL TAB -- */
                    Route::get('import-reconciliation-control-tab/{vat_reg_id}', [TasksController::class, 'loadImportReconciliationControlTab'])->name('import.reconciliation.control.tab');
                /* --end CONTROL TAB -- */
            /* --end IMPORT RECONCILIATION TAB -- */           

            /* -- VAT REG. MAIN TAB -- */ 
                /* -- VAT REG. MAIN:CREATE, STORE, EDIT, UPDATE, DELETE -- */        
                Route::resource('vat-registration-main', VATRegistrationMainController::class);
                /* --end VAT REG. MAIN:CREATE, STORE, EDIT, UPDATE, DELETE -- */

                /* -- VAT REG. MAIN:VIEW -- */
                Route::get('client-vat-registration-main/{client_id}', [VATRegistrationMainController::class, 'getVATRegistrationMain'])->name('vat.registration.main.list');
                /* --end VAT REG. MAIN:VIEW -- */

                /* -- VAT REG. MAIN:CREATE -- */
                Route::get('vat-registration-main/{client_id}/create', [VATRegistrationMainController::class, 'create'])->name('vat.registration.main.client.create'); 
                /* --end VAT REG. MAIN:CREATE -- */

                /* -- VAT REG. MAIN:ERP FIELDS -- */
                Route::get('vat-registration-main/erp-fields/{erp_id}', [VATRegistrationMainController::class, 'loadERPFields'])->name('vat.registration.main.erp.fields');  
                /* --end VAT REG. MAIN:ERP FIELDS -- */

                /* -- VAT REG. MAIN:UPDATE STATUS -- */
                Route::put('vat-registration-main/{vat_reg_main_id}/updatestatus', [VATRegistrationMainController::class, 'updateStatus'])->name('vat.registration.update.status');  
                /* --end VAT REG. MAIN:UPDATE STATUS -- */

                /* -- VAT REG. MAIN:UPDATE CAS -- */
                Route::put('vat-registration-main/{vat_reg_main_id}/updatecashaccountstatement', [VATRegistrationMainController::class, 'updateCashAccountStatement'])->name('vat.registration.update.cash.account.statement');
                /* --end VAT REG. MAIN:UPDATE CAS -- */

                /* -- VAT REG. MAIN:UPDATE DDA -- */
                Route::put('vat-registration-main/{vat_reg_main_id}/updatedutydefermentaccount', [VATRegistrationMainController::class, 'updateDutyDefermentAccount'])->name('vat.registration.update.duty.deferment.account'); 
                /* --end VAT REG. MAIN:UPDATE DDA -- */

                /* -- VAT REG. MAIN:UPDATE OSS -- */
                Route::put('vat-registration-main/{vat_reg_main_id}/updateoss', [VATRegistrationMainController::class, 'updateOSS'])->name('vat.registration.update.oss');
                /* --end VAT REG. MAIN:UPDATE OSS -- */

                /* -- VAT REG. MAIN:UPDATE EXCISE DUTY -- */
                Route::put('vat-registration-main/{vat_reg_main_id}/updateexciseduty', [VATRegistrationMainController::class, 'updateExciseDuty'])->name('vat.registration.update.excise.duty');
                /* --end VAT REG. MAIN:UPDATE EXCISE DUTY -- */
            /* --end VAT REG. MAIN TAB -- */

            /* -- COMPANY TAB -- */
                /* -- COMPANY:UPDATE -- */       
                //Route::put('company/{client_id}', [CompanyController::class, 'updateCompany'])->name('company.update');
                Route::match(['PUT', 'POST'], 'company/{client_id}', [CompanyController::class, 'updateCompany'])->name('company.update');
                /* --end COMPANY:UPDATE -- */

                /* -- COMPANY:FILES -- */  
                Route::get('company/files/{client_id}', [CompanyController::class, 'loadCompanyFilesFromOneDrive'])->name('company.load.files');
                /* --end COMPANY:FILES -- */ 

                /* -- COMPANY:FILE:STORE -- */ 
                Route::post('company/files/{client_id}', [CompanyController::class, 'uploadCompanyFilesToOneDrive'])->name('company.upload.files');
                /* --end COMPANY:FILE:STORE -- */

                /* -- COMPANY:FILE:DELETE -- */
                Route::delete('company/files/{file_id}', [CompanyController::class, 'deleteCompanyFilesFromOneDrive'])->name('company.delete.files');
                /* --end COMPANY:FILE:DELETE -- */

                /* -- COMPANY:QA:VIEW -- */
                Route::get('company/qa/{client_id}', [CompanyController::class, 'loadCompanyQATab'])->name('company.view.qa');
                /* --end COMPANY:QA:VIEW -- */

                /* -- COMPANY:QA:DELETE -- */
                Route::delete('company/qa/{qa_id}', [CompanyController::class, 'deleteCompanyQA'])->name('company.delete.qa');
                /* --end COMPANY:QA:DELETE -- */

                /* -- COMPANY:HISTORY:VIEW -- */
                Route::get('company/history/{client_id}', [CompanyController::class, 'loadCompanyHistoryTab'])->name('company.view.history');
                /* --end COMPANY:HISTORY:VIEW -- */

                /* -- COMPANY:EXTRA FIELDS:DELETE -- */
                Route::delete('company/extrafield/{extra_id}', [CompanyController::class, 'deleteCompanyExtraFields'])->name('company.delete.extra.fields');
                /* --end COMPANY:EXTRA FIELDS:DELETE -- */
            /* --end COMPANY TAB -- */

            /* -- COMMENTS TAB -- */
                /* -- COMMENTS:VIEW -- */
                Route::get('company/comment/{client_id}', [CompanyController::class, 'loadCompanyComment'])->name('company.comment');
                /* --end COMMENTS:VIEW -- */

                /* -- COMMENT:STORE -- */
                Route::post('company/comment/{client_id}', [CompanyController::class, 'postCompanyComment'])->name('company.comment.store');
                /* --end COMMENT:STORE -- */

                /* -- COMMENT:DELETE -- */
                Route::delete('company/comment/{client_id}', [CompanyController::class, 'deleteCompanyComment'])->name('company.comment.delete');
                /* --end COMMENT:DELETE -- */
            /* --end COMMENTS TAB -- */

            /* -- API CONNECTION TAB -- */
                /* -- CLIENT VAT INFO -- */
                //- DONT DELETE      
                Route::get('client-vat-info/{vat_reg_id}', [VATRegistrationMainController::class, 'getClientVATInfo'])->name('client.vat.info');
                /* --end CLIENT VAT INFO -- */
            /* --end API CONNECTION TAB -- */

            /* -- CONTACTS TAB -- */
                /* -- CONTACTS:VIEW -- */
                //Route::get('contacts/{client_id}', [VATRegistrationController::class, 'getContacts'])->name('contacts.client.user.list');
                /* --end CONTACTS:VIEW -- */    

                /* -- CONTACT:ASSIGN CLIENT USERS -- */
                Route::post('client-user/assign', [CompanyController::class, 'assignClientUser'])->name('dv-clients.assign.client.user');
                /* --end CONTACT:ASSIGN CLIENT USERS -- */

                /* -- CONTACT:ASSIGNED CLIENT USERS -- */
                Route::get('client-user/assigned/{client_id}', [CompanyController::class, 'assignedClientUser'])->name('dv-clients.assigned.client.user'); 
                /* --end CONTACT:ASSIGNED CLIENT USERS -- */
            /* --end CONTACTS TAB -- */

        /* --end COMPANY -- */        

        /* -- BULK UPLOAD -- */
        Route::get('bulk-upload', [VATRegistrationController::class, 'bulkUploadIndex'])->name('bulk.upload.index');   
        Route::post('bulk-upload', [VATRegistrationController::class, 'bulkUpload'])->name('bulk.upload');
        Route::post('bulk-email', [VATRegistrationController::class, 'sendBulkEmailFileToClientUser'])->name('bulk.email');
        /* --end BULK UPLOAD -- */

        //Route::post('send-email', [VATRegistrationController::class, 'sendEmailToClientUser'])->name('client.vat.draft.email');
        //Route::post('send-lock-email', [VATRegistrationController::class, 'sendLockEmailToClientUser'])->name('client.vat.lock.email');                    
        
        // Route::get('vat-return/file/{vat_reg_id}', [VATRegistrationController::class, 'loadVatReturnFile'])->name('vat.return.file.load');    
        // Route::post('vat-return/file/{vat_reg_id}', [VATRegistrationController::class, 'uploadVatReturnFileToOneDrive'])->name('vat.return.file.upload'); 
        // Route::get('vat-return/file/{file_id}/download', [VATRegistrationController::class, 'downloadVatReturnFileFromOneDrive'])->name('vat.return.file.download');    
        // Route::delete('vat-return/file/{file_id}', [VATRegistrationController::class, 'deleteVatReturnFileFromOneDrive'])->name('vat.return.file.delete');  
        
        //Route::get('submittingfields/{vat_reg_id}', [VATRegistrationController::class, 'loadSubmittingFields'])->name('submitting.fields.load');

        //Route::get('history/{id}/download', [VATRegistrationController::class, 'downloadFromOneDrive'])->name('history.file.download');  
        //Route::get('history/{vat_reg_id}', [VATRegistrationController::class, 'loadHistory'])->name('history.load');    

        /* -- DECLARATION -- */
        Route::get('declaration', [DeclarationController::class, 'dummy'])->name('declaration');   

        /* -- JOB LOG -- */        
        Route::get('job-status/{logId}', [DeclarationController::class, 'jobStatus'])->name('job.status');
        /* --end JOB LOG -- */
        /* --end DECLARATION -- */     
    });  
    /* --end ROLE: SUPER-ADMIN|TEAM-USER|CLIENT-USER|COMPANY-ADMIN -- */

    /* -- ROLE: CLIENT-USER -- */
    Route::group(['middleware' => ['role:client-user']], function () {
        /* -- CLIENT-USER TASKS -- */
        Route::get('clientuser-tasks', [TasksController::class, 'clientUserTasks'])->name('clientuser.tasks');
        /* --end CLIENT-USER TASKS -- */
    });  
    /* --end ROLE: CLIENT-USER -- */
});
/* --end WITH AUTH -- */

/* -- WITHOUT AUTH -- */
    /* -- REGISTER -- */     
        Route::post('register', [RegisterController::class, 'store'])->name('register.store');

        /* -- COMPANY:FILES -- */  
        Route::get('register/files/{client_id}', [RegisterController::class, 'loadCompanyFilesFromOneDrive'])->name('register.company.load.files');        
        /* --end COMPANY:FILES -- */

        /* -- COMPANY:FILE:STORE -- */ 
        Route::post('register/files/{client_id}', [RegisterController::class, 'uploadCompanyFilesToOneDrive'])->name('register.company.upload.files');
        /* --end COMPANY:FILE:STORE -- */
    /* --end REGISTER -- */  

    /* -- CONFIRM - APPROVE NUMBERS -- */
        /* -- CONFIRM:VIEW -- */
        Route::get('confirm-numbers/{vat_reg_id}', [ConfirmController::class, 'confirmNumbersFromClient'])->name('client.vat.email.confirm');
        /* --end CONFIRM:VIEW -- */

        /* -- CONFIRM:ACCEPT -- */
        Route::post('confirm-numbers/{vat_reg_id}', [ConfirmController::class, 'acceptNumbersFromClient'])->name('client.vat.email.accept');
        /* --end CONFIRM:ACCEPT -- */

        /* -- CONFIRM:DECLINE -- */
        Route::delete('confirm-numbers/{vat_reg_id}', [ConfirmController::class, 'declineNumbersFromClient'])->name('client.vat.email.decline');
        /* --end CONFIRM:DECLINE -- */

        /* -- CONFIRM:EXPORT -- */
        Route::post('pdf-confirm-view/{vat_reg_id}/export', [ConfirmController::class, 'exportPdfConfirmView'])->name('client.vat.confirm.pdf');
        /* --end CONFIRM:EXPORT -- */
    /* --end CONFIRM - APPROVE NUMBERS -- */

    /* -- AWS NOTIFICATION HANDLER -- */
    Route::post('aws-sns/email-notification', [ConfirmController::class, 'handleAwsNotification']);
    /* --end AWS NOTIFICATION HANDLER -- */  

    /* -- CC TRACKING -- */
    Route::get('track/open', [ConfirmController::class, 'handleCCTracking'])->name('cc.track.open');
    //Route::get('track/click', [ConfirmController::class, 'handleCCTracking'])->name('cc.track.click');
    /* --end CC TRACKING -- */

    /* -- SELECT ROLE -- */
    Route::get('/select-role', [RoleController::class, 'selectRole'])->name('select.role');
    Route::post('/set-role', [RoleController::class, 'setRole'])->name('set.role');
    /* --end SELECT ROLE -- */

    /* -- CLEAR CACHE -- */    
    if(strtolower(env('APP_URL')) !== "https://app.intravat.cloud" || strtolower(config('app.url')) !== "https://app.intravat.cloud")
    {
        Route::get('clear-cache', function() {
            Artisan::call('cache:clear');  
            Artisan::call('route:clear');
            Artisan::call('config:clear');      
            Artisan::call('config:cache');  
            Artisan::call('view:clear');  
            Artisan::call('event:clear');
            return "Cache is cleared";
        });
    }
    /* --end CLEAR CACHE -- */
/* --end WITHOUT AUTH -- */