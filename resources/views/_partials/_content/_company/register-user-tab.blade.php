<div class="row g-3">
  <div class="col-sm-6">
    <label class="form-label" for="multiStepsFirstname">First Name</label>
    <input type="text" name="multiStepsFirstname" id="multiStepsFirstname" class="form-control" placeholder="johndoe" />
  </div>

  <div class="col-sm-6">
    <label class="form-label" for="multiStepsLastname">Last Name</label>
    <input type="text" name="multiStepsLastname" id="multiStepsLastname" class="form-control" placeholder="doe" />
  </div>

  <div class="col-sm-6">
    <label class="form-label" for="multiStepsUserEmail">Email</label>
    <input type="email" name="multiStepsUserEmail" id="multiStepsUserEmail" class="form-control" placeholder="john.doe@email.com" aria-label="john.doe" />
  </div>

  <div class="col-sm-6">
    <label class="form-label" for="multiStepsUserTelephone">Telephone</label>          
    <input type="text" id="multiStepsUserTelephone" class="form-control phone-mask telephone" placeholder="00 00 00 00" aria-label="00 00 00 00" name="multiStepsUserTelephone" /> 
  </div> 

  <div class="col-sm-6 form-password-toggle">
    <label class="form-label" for="multiStepsPass">Password</label>
    <div class="input-group input-group-merge">
      <input type="password" id="multiStepsPass" name="multiStepsPass" class="form-control" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="multiStepsPass2" />
      <span class="input-group-text cursor-pointer" id="multiStepsPass2"><i class="bx bx-hide"></i></span>
    </div>
  </div>

  <div class="col-sm-6 form-password-toggle">
    <label class="form-label" for="multiStepsConfirmPass">Confirm Password</label>
    <div class="input-group input-group-merge">
      <input type="password" id="multiStepsConfirmPass" name="multiStepsConfirmPass" class="form-control" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="multiStepsConfirmPass2" />
      <span class="input-group-text cursor-pointer" id="multiStepsConfirmPass2"><i class="bx bx-hide"></i></span>
    </div>
  </div>

  <div class="col-12 d-flex justify-content-between mt-4">
    <button class="btn btn-label-secondary btn-prev" disabled> <i class="bx bx-chevron-left bx-sm ms-sm-n2"></i>
    <span class="d-sm-inline-block d-none">Previous</span></button>
    <button class="btn btn-primary btn-next"> <span class="d-sm-inline-block d-none me-sm-1 me-0">Next</span> <i class="bx bx-chevron-right bx-sm me-sm-n2"></i></button>
  </div>
</div>
