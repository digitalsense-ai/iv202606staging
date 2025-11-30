<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dv_submitting_fields_ch', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('vat_reg_id')->unsigned();

            //Consideration
            $table->longText('box_200')->comment('Total amount of agreed or collected consideration incl. from supplies opted for taxation, transfer of supplies acc. to the notification procedure and supplies provided abroad (worldwide turnover)')->nullable();
            $table->longText('box_205')->comment('Consideration reported in box 200 from supplies exempt from the tax without credit (art. 21) where the option for their taxation according to art. 22 has been exercised')->nullable();

            //Deductions
            $table->longText('box_220')->comment('Supplies exempt from the tax (e.g. export, art. 23) and supplies provided to institutional and individual beneficiaries that are exempt from liability for tax (art. 107 para. 1 lit. a)')->nullable();           
            $table->longText('box_221')->comment('Supplies provided abroad (place of supply is abroad)')->nullable();
            $table->longText('box_225')->comment('Transfer according to the notification procedure (art. 38, please submit Form. 764)')->nullable();
            $table->longText('box_230')->comment('Supplies provided on Swiss territory exempt from the tax without credit (art. 21) and where the option for their taxation according to art. 22 has not been exercised')->nullable();
            $table->longText('box_235')->comment('Reduction of consideration (discounts, rebates etc.)')->nullable();
            $table->longText('box_280')->comment('Miscellaneous (e. g. land value, purchase prices in case of margir taxation)')->nullable();
            $table->longText('box_289')->comment('Miscellaneous (e. g. land value, purchase prices in case of margir taxation)')->nullable();

            //Taxable turnover
            $table->longText('box_299')->comment('Taxable turnover (Ref. 200 minus Ref. 289)')->nullable();

            $table->longText('box_303')->comment('Standard rate supplies')->nullable();
            $table->longText('box_303_1')->comment('Standard rate supplies tax')->nullable();
            $table->longText('box_313')->comment('Reduced rate supplies')->nullable();
            $table->longText('box_313_1')->comment('Reduced rate supplies tax')->nullable();
            $table->longText('box_343')->comment('Accommodation')->nullable();
            $table->longText('box_343_1')->comment('Accommodation tax')->nullable();

            $table->longText('box_379')->comment('Taxable turnover (As in Ref. 299)')->nullable();
            $table->longText('box_383')->comment('Acquisition tax (net)')->nullable();
            $table->longText('box_383_1')->comment('Acquisition tax (exklusive VAT)')->nullable();

            $table->longText('box_399')->comment('Total amount of tax due (Ref. 303 to 383)')->nullable();

            $table->longText('box_400')->comment('Input tax on cost of materials and supplies of services')->nullable();
            $table->longText('box_405')->comment('Input tax on investments and other operating costs')->nullable();
            $table->longText('box_410')->comment('De-taxation (art. 32, please enclose a detailed list) and corrections following a change from the net tax method or flat-rate to the effective method.')->nullable();
            $table->longText('box_415')->comment('Correction of the input tax deduction: mixed use (art. 30), own use (art. 31) and corrections following a change from the effective method to the net tax or flat-rate method.')->nullable();
            $table->longText('box_420')->comment('Reduction of the input tax deduction: Flow of funds, which are not deemed to be consideration, such as subsidies, tourist charges (art. 33 para. 2)')->nullable();
            $table->longText('box_479')->comment('Reduction of the input tax deduction: Flow of funds, which are not deemed to be consideration, such as subsidies, tourist charges (art. 33 para. 2)')->nullable();
            $table->longText('box_500')->comment('Amount payable')->nullable();
            $table->longText('box_510')->comment('Credit in favour of the taxable person')->nullable();
           
            $table->timestamps();

            $table->foreign('vat_reg_id', 'dv_submitting_fields_ch_vat_reg_id_fk')
                ->references('id')
                ->on('dv_vat_registration')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dv_submitting_fields_ch');
    }
};
