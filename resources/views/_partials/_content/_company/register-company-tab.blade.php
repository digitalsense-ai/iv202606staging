<!-- <div class="row g-3"> -->

  <div class="input-group">
    <div class="form-floating">
      <input type="text" class="form-control" id="multiStepsVatNo" name="multiStepsVatNo" placeholder="83097515" aria-describedby="vatnoHelp"  />
      <label for="multiStepsVatNo">Company registration number</label>          
    </div>
    <button class="btn btn-outline-primary" type="button" id="btn_vat_search">Search</button>          
  </div>
  <div id="clientNameHelp" class="form-text mb-3">Search for the Company registration number to fill the details.</div>

  <div class="form-floating mb-3">
    <input type="text" class="form-control" id="multiStepsCompanyName" name="multiStepsCompanyName" placeholder="ACME Inc." aria-describedby="clientNameHelp"  />
    <label for="multiStepsCompanyName">Company Name</label>           
  </div>

  <div class="form-floating mb-3">
    <input type="text" class="form-control" id="multiStepsAddress" name="multiStepsAddress" placeholder="Street name, house number etc.," aria-describedby="clientAddressHelp"  />
    <label for="multiStepsAddress">Address</label>          
  </div>

  <div class="form-floating mb-3">
    <input type="text" class="form-control" id="multiStepsZipcode" name="multiStepsZipcode" placeholder="2000" aria-describedby="offPostcodeHelp"  />
    <label for="multiStepsZipcode">Zipcode</label>          
  </div>

  <div class="form-floating mb-3">
    <input type="text" class="form-control" id="multiStepsCity" name="multiStepsCity" placeholder="Copenhagen" aria-describedby="offCityHelp"  />
    <label for="multiStepsCity">City</label>          
  </div>
 
  <div class="form-floating mb-3">
    <select id="multiStepsState" class="form-select" data-allow-clear="true" name="multiStepsState" >
      <option value="">Select</option>
      <optgroup label="Europe">  
        <option value="AT">Austria</option>
        <option value="BE">Belgium</option>
        <option value="BG">Bulgaria</option>
        <option value="HR">Croatia</option>
        <option value="CY">Cyprus</option>
        <option value="CZ">Czech Republic</option>
        <option value="DK" selected>Denmark</option>
        <option value="EE">Estonia</option>
        <option value="FI">Finland</option>
        <option value="FR">France</option>
        <option value="DE">Germany</option>
        <option value="GR">Greece</option>
        <option value="HU">Hungary</option>
        <option value="IE">Ireland, Republic of (EIRE)</option>
        <option value="IT">Italy</option>
        <option value="LV">Latvia</option>
        <option value="LT">Lithuania</option>
        <option value="LU">Luxembourg</option>
        <option value="MT">Malta</option>
        <option value="NL">Netherlands</option>
        <option value="NO">Norway</option>             
        <option value="PL">Poland</option>
        <option value="PT">Portugal</option>
        <option value="RO">Romania</option>
        <option value="SK">Slovakia</option>
        <option value="SI">Slovenia</option>
        <option value="ES">Spain</option>
        <option value="SE">Sweden</option>
        <option value="CH">Switzerland</option>
        <option value="GB">United Kingdom</option>
      </optgroup>
      <optgroup label="Rest of the world">
        <option value="US">United States of America</option>
        <option value="HK">Hong Kong</option>
      </optgroup>
    </select>
    <label for="multiStepsState">Country</label>          
  </div>

  <div class="form-floating mb-3">
    <input type="email" class="form-control" id="multiStepsCompEmail" name="multiStepsCompEmail" placeholder="john.doe" aria-describedby="lrepEmailHelp"  />
    <label for="multiStepsCompEmail">Email</label>          
  </div>

  <div class="form-floating mb-3">
    <input type="text" class="form-control telephone" id="multiStepsTelephone" name="multiStepsTelephone" placeholder="658 799 8941" aria-describedby="telephoneHelp"  />        
  </div>

  <div class="form-floating mb-3">
    <input type="text" class="form-control" id="multiStepsCompDesc" name="multiStepsCompDesc" placeholder="Anpartsselskab" aria-describedby="lrepAddressHelp"  />
    <label for="multiStepsCompDesc">Company Desc.</label>          
  </div>

  <div class="form-floating mb-3">
    <input type="text" class="form-control" id="multiStepsEmployees" name="multiStepsEmployees" placeholder="10" aria-describedby="employeesHelp" />
    <label for="multiStepsEmployees">Employees</label>          
  </div>

  <div class="form-floating mb-3">
    <input type="text" class="form-control" id="multiStepsStartDate" name="multiStepsStartDate" placeholder="DD-MM-YYYY" aria-describedby="startDateHelp" />
    <label for="multiStepsStartDate">Start Date</label>          
  </div>

  <div class="form-floating mb-3">
    <input type="text" class="form-control" id="multiStepsEndDate" name="multiStepsEndDate" placeholder="DD-MM-YYYY" aria-describedby="endDateHelp" />
    <label for="multiStepsEndDate">End Date</label>          
  </div>

  <div class="col-12 d-flex justify-content-between mt-4">
    <button class="btn btn-primary btn-prev"> <i class="bx bx-chevron-left bx-sm ms-sm-n2"></i><span class="d-sm-inline-block d-none">Previous</span></button>
    <button class="btn btn-primary btn-next"> <span class="d-sm-inline-block d-none me-sm-1 me-0">Next</span> <i class="bx bx-chevron-right bx-sm me-sm-n2"></i></button>
  </div>

<!-- </div> -->               