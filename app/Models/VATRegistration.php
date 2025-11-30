<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VATRegistration extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dv_vat_registration';

    protected $guarded = []; 

    /**
     * Get the vatregmain for the vat reg.
     */
    public function vatregmain()
    {        
        return $this->belongsTo('App\Models\VATRegistrationMain', 'vat_reg_main_id');
    }

    /**
     * Get the client for the vat reg.
     */
    public function client()
    {        
        return $this->belongsTo('App\Models\Client', 'client_id');
    }

    /**
     * Get the invoices for the vat reg.
     */
    public function invoices()
    {        
        return $this->hasMany('App\Models\Invoices', 'vat_reg_id');
    }

    /**
     * Get the documents for the vat reg.
     */
    public function documents()
    {        
        return $this->hasMany('App\Models\Documents', 'vat_reg_id')->where('doc_type','<>', 'C79');
    }

    /**
     * Get the c79 for the vat reg.
     */
    public function c79()
    {
        return $this->hasMany('App\Models\Documents', 'vat_reg_id')->where('doc_type','=', 'C79');        
    }

    /**
     * Get the cas for the vat reg.
     */
    public function cas()
    {
        return $this->hasMany('App\Models\CashAccountStatement', 'vat_reg_id');        
    }

    /**
     * Get the dda for the vat reg.
     */
    public function dda()
    {
        return $this->hasMany('App\Models\DutyDefermentAccount', 'vat_reg_id');        
    }

    /**
     * Get the importvatfiles for the vat reg.
     */
    public function importvatfiles()
    {
        return $this->hasMany('App\Models\ImportVatFiles', 'vat_reg_id')->orderBy('file_type')->orderBy('month_year');
    }

    /**
     * Get the commercialinvoicesfiles for the vat reg.
     */
    public function commercialinvoicesfiles()
    {
        return $this->hasMany('App\Models\CommercialInvoiceFiles', 'vat_reg_id');        
    }

    /**
     * Get the pivs for the vat reg.
     */
    public function pivs()
    {
        return $this->hasMany('App\Models\Pivs', 'vat_reg_id');        
    }

    /**
     * Get the receipt for the vat reg.
     */
    public function receipt()
    {
        return $this->hasMany('App\Models\Receipt', 'vat_reg_id');        
    }

    /**
     * Get the filesemailnote for the vat reg.
     */
    public function filesemailnote()
    {
        return $this->hasMany('App\Models\FilesEmailNote', 'vat_reg_id');        
    }

    /**
     * Get the vatreturnfiles for the vat reg.
     */
    public function vatreturnfiles()
    {
        return $this->hasMany('App\Models\VATReturnFiles', 'vat_reg_id');        
    }

    /**
     * Get the vatreturnofiles for the vat reg.
     */
    public function vatreturnofiles()
    {
        return $this->hasMany('App\Models\VATReturnOFiles', 'vat_reg_id');        
    }

    /**
     * Get the vatreturns for the vat reg.
     */
    public function vatreturns()
    {
        return $this->hasMany('App\Models\VATReturns', 'vat_reg_id')->orderBy('invoice_type','desc')->orderBy('vat_percentage','desc');
    }

    /**
     * Get the submittingfields for the vat reg.
     */
    public function submittingfields()
    {
        return $this->hasOne('App\Models\SubmittingFields', 'vat_reg_id');        
    }

    /**
     * Get the submittingfieldsNO for the vat reg.
     */
    public function submittingfieldsNO()
    {
        return $this->hasOne('App\Models\SubmittingFieldsNO', 'vat_reg_id');        
    }

    /**
     * Get the submittingfieldsCH for the vat reg.
     */
    public function submittingfieldsCH()
    {
        return $this->hasOne('App\Models\SubmittingFieldsCH', 'vat_reg_id');        
    }

    /**
     * Get the uservatreg for the vat reg.
     */
    public function uservatreg()
    {
        return $this->hasMany('App\Models\UserVATRegistration', 'vat_reg_id');        
    }   

    /**
     * Get the vatreturncomments for the vat reg.
     */
    public function vatreturncomments()
    {
        return $this->hasMany('App\Models\VATReturnComments', 'vat_reg_id');        
    }

    /**
     * Get the vatreturncommentfiles for the vat reg.
     */
    public function vatreturncommentfiles()
    {
        return $this->hasMany('App\Models\VATReturnCommentFiles', 'vat_reg_id');        
    }

    /**
     * Get the email notification for the vat reg.
     */
    public function emailnotification()
    {        
        return $this->hasMany('App\Models\EmailNotification', 'vat_reg_id');
    }
    
    /**
     * Get the user for the vat reg. created_by
     */
    public function draftcreatedby()
    {        
        return $this->belongsTo('App\Models\User', 'created_by', 'id');
    }

    /**
     * Get the user for the vat reg. email_by
     */
    public function assignedto()
    {        
        return $this->belongsTo('App\Models\User', 'email_by', 'id');
    }

    /**
     * Get the user for the vat reg. email_by
     */
    public function emailsentto()
    {        
        return $this->belongsTo('App\Models\User', 'email_by', 'id');
    }

    /**
     * Get the user for the vat reg. approved_by
     */
    public function approvedby()
    {        
        return $this->belongsTo('App\Models\User', 'approved_by', 'id');
    }

    /**
     * Get the user for the vat reg. approved_by
     */
    public function declinedby()
    {        
        return $this->belongsTo('App\Models\User', 'approved_by', 'id');
    }

    // /**
    //  * Get the template for the vat reg.
    //  */
    // public function excelcolumntemplate()
    // {        
    //     return $this->belongsTo('App\Models\ExcelColumnTemplates', 'excel_column_template_id');
    // }

    /**
     * Get the anyexcel template for the vat reg.
     */
    public function anyexceltemplate()
    {        
        return $this->belongsTo('App\Models\AnyExcelTemplates', 'anyexcel_template_id');
    }

    /**
     * Get the importreconciliationfiles for the vat reg.
     */
    public function importreconciliationfiles()
    {
        return $this->hasMany('App\Models\ImportReconciliationFiles', 'vat_reg_id');        
    }      

    /**
     * Get the importreconciliationanyexcelfiles for the vat reg.
     */
    public function importreconciliationanyexcelfiles()
    {
        return $this->hasMany('App\Models\ImportReconciliationAnyExcelFiles', 'vat_reg_id');        
    }
    
    /**
     * Get the importreconciliationswissfiles for the vat reg.
     */
    public function importreconciliationswissfiles()
    {
        return $this->hasMany('App\Models\ImportReconciliationSwissFiles', 'vat_reg_id');        
    }   
    
    /**
     * Get the importreconciliationcominvoices for the vat reg.
     */
    public function importreconciliationcominvoices()
    {        
        return $this->hasMany('App\Models\ImportReconciliationComInvoices', 'vat_reg_id');
    }

    /**
     * Get the importreconciliationsalesinvoices for the vat reg.
     */
    public function importreconciliationsalesinvoices()
    {        
        return $this->hasMany('App\Models\ImportReconciliationSalesInvoices', 'vat_reg_id')->orderBy('invoice_no','asc');
    }

    /**
     * Get the notes for the vat reg.
     */
    public function vatreturnnotes()
    {        
        return $this->hasMany('App\Models\VATReturnNotes', 'vat_reg_id');
    }

    /**
     * Get the notes for the import reconciliation
     */
    public function importreconciliationnotes()
    {        
        return $this->hasMany('App\Models\ImportReconciliationNotes', 'vat_reg_id');
    }

    /**
     * Get the vatcontrolfiles for the vat reg.
     */
    public function vatcontrolfiles()
    {
        return $this->hasMany('App\Models\VATControlFiles', 'vat_reg_id');        
    }

    /**
     * Get the vatcontrolofiles for the vat reg.
     */
    public function vatcontrolofiles()
    {
        return $this->hasMany('App\Models\VATControlOFiles', 'vat_reg_id');        
    }

    /**
     * Get the ircontrolfiles for the vat reg.
     */
    public function ircontrolfiles()
    {
        return $this->hasMany('App\Models\ImportReconciliationControlFiles', 'vat_reg_id');        
    }

    /**
     * Get the ircontrolofiles for the vat reg.
     */
    public function ircontrolofiles()
    {
        return $this->hasMany('App\Models\ImportReconciliationControlOFiles', 'vat_reg_id');        
    }
}
