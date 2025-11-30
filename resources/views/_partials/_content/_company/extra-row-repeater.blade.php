<div data-extra_id="{{ isset($clientextrafield) ? $clientextrafield->id : ''}}" data-repeater-item>
  <input type="hidden" name="extra_id" value="{{ isset($clientextrafield) ? $clientextrafield->id : ''}}">
  <div class="row mb-3">
    <div class="col-lg-10 col-xl-10 col-10">
      <div class="input-group"> 
        <div class="form-floating">
          <input type="text" aria-label="Subject" class="form-control subject" id="extra_subject-{{ isset($clientextrafield) ? $clientextrafieldkey : '0'}}-1" name="extra_subject" placeholder="Field 1" aria-describedby="extraSubjectHelp" value="{{ isset($clientextrafield) ? $clientextrafield->subject : ''}}" required>
          <label for="extra_subject">Subject</label>           
        </div>

        <div class="form-floating">
          <input type="text" class="form-control" id="extra_value-{{ isset($clientextrafield) ? $clientextrafieldkey : '0'}}-2" name="extra_value" placeholder="Lorem Ipsum" aria-describedby="extraValueHelp" value="{{ isset($clientextrafield) ? $clientextrafield->value : ''}}" required />
          <label for="extra_value">Value</label>           
        </div> 
      </div>
    </div>

    <div class="col-lg-2 col-xl-2 col-2 p-0 d-flex align-items-center">
      <span class="btn btn-label-danger px-2" data-repeater-delete>
        <i class="bx bx-x me-1"></i>
        <span class="align-middle">Delete</span>
      </span>
    </div> 
</div>

</div>