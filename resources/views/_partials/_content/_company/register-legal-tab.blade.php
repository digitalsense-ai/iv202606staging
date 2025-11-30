  <div class="row g-3">
    <div class="col-sm-6">
      <label class="form-label" for="multiStepsRepFirstName">First Name</label>
      <input type="text" name="multiStepsRepFirstName" id="multiStepsRepFirstName" class="form-control" placeholder="" />
    </div>

    <div class="col-sm-6">
      <label class="form-label" for="multiStepsSurname">Surname</label>
      <input type="text" class="form-control" id="multiStepsSurname" name="multiStepsSurname" placeholder="Doe" aria-describedby="lrepLnameHelp"  />
    </div>

    <div class="col-sm-6">
      <label class="form-label" for="multiStepsRepAddress">Address</label>
      <input type="text" class="form-control" id="multiStepsRepAddress" name="multiStepsRepAddress" placeholder="Street name, house number etc.," aria-describedby="lrepAddressHelp"  />
    </div>

    <div class="col-sm-6">
      <label class="form-label" for="multiStepsRepZipcode">Zipcode</label>
      <input type="text" class="form-control" id="multiStepsRepZipcode" name="multiStepsRepZipcode" placeholder="2000" aria-describedby="lrepPostcodeHelp"  />
    </div>

    <div class="col-sm-6">
      <label class="form-label" for="multiStepsRepCity">City</label>
      <input type="text" class="form-control" id="multiStepsRepCity" name="multiStepsRepCity" placeholder="Copenhagen" aria-describedby="lrepCityHelp"  />
    </div>
  </div>

  <div class="content-header my-4">
    <h4>Additional Information</h4>                    
  </div> 

  <div class="row g-3">         
    <div class="col-sm-6">
      <label for="multiStepsRiskAssessment">Risk Assessment</label>
      <select id="multiStepsRiskAssessment" class="form-select" data-allow-clear="true" name="multiStepsRiskAssessment" >
        <option value="">Select</option>
        <option value="Low">Low</option>
        <option value="Medium">Medium</option>
        <option value="High">High</option>              
      </select>
    </div> 

    <div class="col-sm-6">
      <label for="multiStepsUseTrademark">Allowed to use trademark</label>   
      <select id="multiStepsUseTrademark" class="form-select" data-allow-clear="true" name="multiStepsUseTrademark" >
        <option value="">Select</option>
        <option value="1">Yes</option>
        <option value="0">No</option>              
      </select> 
    </div>

    <div class="col-sm-6">
      <label for="multiStepsTradingName">Trading name</label>  
      <input type="text" class="form-control" id="multiStepsTradingName" name="multiStepsTradingName" placeholder="" aria-describedby="tradingNameHelp" />
    </div>   
  </div>

  <div class="content-header my-4">
    <h4>Billing</h4>                    
  </div> 

  <div class="row g-3">         
    <div class="col-sm-6">
      <label for="multiStepsEconomicsId">E-Conomics id</label>
      <input type="text" class="form-control" id="multiStepsEconomicsId" name="multiStepsEconomicsId" placeholder="" aria-describedby="economicsIdHelp" />   
    </div> 

    <div class="col-sm-6">
      <label for="multiStepsAdmFee">Adm. fee</label>
      <input type="text" class="form-control" id="multiStepsAdmFee" name="multiStepsAdmFee" placeholder="" aria-describedby="admFeeHelp" />   
    </div>

    <div class="col-sm-6">
      <label for="multiStepsConsultancyLow">Consultancy low</label>
      <input type="text" class="form-control" id="multiStepsConsultancyLow" name="multiStepsConsultancyLow" placeholder="" aria-describedby="consultancyLowHelp" />   
    </div> 

    <div class="col-sm-6">
      <label for="multiStepsConsultancyHigh">Consultancy high</label>
      <input type="text" class="form-control" id="multiStepsConsultancyHigh" name="multiStepsConsultancyHigh" placeholder="" aria-describedby="consultancyHighHelp" />   
    </div> 

    <div class="col-12 d-flex justify-content-between mt-4 register-buttons">
      <button class="btn btn-primary btn-prev"> <i class="bx bx-chevron-left bx-sm ms-sm-n2"></i> <span class="d-sm-inline-block d-none">Previous</span></button>
      <button type="submit" class="btn btn-success btn-next btn-submit">Submit</button>
    </div>
  </div>

</form>