<div data-repeater-item>
            
  <div class="row formula-row mb-3">
    <div class="col-lg-6 col-xl-3 col-12">
      <label class="form-label" for="arithmetic">Arithmetic</label>
      <select id="form-formula-repeater-0-1" name="arithmetic" class="form-select arithmetic">
        <option value="">-- Select --</option>
        <option value="+">+</option>
        <option value="-">-</option>
        <option value="*">*</option>
        <option value="/">/</option>           
      </select>
    </div>

    <div class="col-lg-6 col-xl-3 col-12">  
      <label class="form-label" for="column">Column</label>          

      <select id="form-formula-repeater-0-2" name="column" class="form-select column">
        <option value="">-- Select Column / Type Value --</option>
        <option value="type_value">Type Value</option>
        <option value="A" {{ isset($special) ? (($special['column_1'] == 'A') ? 'selected' : '') : '' }}>Column A</option>
        <option value="B" {{ isset($special) ? (($special['column_1'] == 'B') ? 'selected' : '') : '' }}>Column B</option>
        <option value="C" {{ isset($special) ? (($special['column_1'] == 'C') ? 'selected' : '') : '' }}>Column C</option>
        <option value="D" {{ isset($special) ? (($special['column_1'] == 'D') ? 'selected' : '') : '' }}>Column D</option>
        <option value="E" {{ isset($special) ? (($special['column_1'] == 'E') ? 'selected' : '') : '' }}>Column E</option>
        <option value="F" {{ isset($special) ? (($special['column_1'] == 'F') ? 'selected' : '') : '' }}>Column F</option>
        <option value="G" {{ isset($special) ? (($special['column_1'] == 'G') ? 'selected' : '') : '' }}>Column G</option>
        <option value="H" {{ isset($special) ? (($special['column_1'] == 'H') ? 'selected' : '') : '' }}>Column H</option>
        <option value="I" {{ isset($special) ? (($special['column_1'] == 'I') ? 'selected' : '') : '' }}>Column I</option>
        <option value="J" {{ isset($special) ? (($special['column_1'] == 'J') ? 'selected' : '') : '' }}>Column J</option>
        <option value="K" {{ isset($special) ? (($special['column_1'] == 'K') ? 'selected' : '') : '' }}>Column K</option>
        <option value="L" {{ isset($special) ? (($special['column_1'] == 'L') ? 'selected' : '') : '' }}>Column L</option>
        <option value="M" {{ isset($special) ? (($special['column_1'] == 'M') ? 'selected' : '') : '' }}>Column M</option>
        <option value="N" {{ isset($special) ? (($special['column_1'] == 'N') ? 'selected' : '') : '' }}>Column N</option>
        <option value="O" {{ isset($special) ? (($special['column_1'] == 'O') ? 'selected' : '') : '' }}>Column O</option>
        <option value="P" {{ isset($special) ? (($special['column_1'] == 'P') ? 'selected' : '') : '' }}>Column P</option>
        <option value="Q" {{ isset($special) ? (($special['column_1'] == 'Q') ? 'selected' : '') : '' }}>Column Q</option>
        <option value="R" {{ isset($special) ? (($special['column_1'] == 'R') ? 'selected' : '') : '' }}>Column R</option>
        <option value="S" {{ isset($special) ? (($special['column_1'] == 'S') ? 'selected' : '') : '' }}>Column S</option>
        <option value="T" {{ isset($special) ? (($special['column_1'] == 'T') ? 'selected' : '') : '' }}>Column T</option>
        <option value="U" {{ isset($special) ? (($special['column_1'] == 'U') ? 'selected' : '') : '' }}>Column U</option>
        <option value="V" {{ isset($special) ? (($special['column_1'] == 'V') ? 'selected' : '') : '' }}>Column V</option>
        <option value="W" {{ isset($special) ? (($special['column_1'] == 'W') ? 'selected' : '') : '' }}>Column W</option>
        <option value="X" {{ isset($special) ? (($special['column_1'] == 'X') ? 'selected' : '') : '' }}>Column X</option>
        <option value="Y" {{ isset($special) ? (($special['column_1'] == 'Y') ? 'selected' : '') : '' }}>Column Y</option>
        <option value="Z" {{ isset($special) ? (($special['column_1'] == 'Z') ? 'selected' : '') : '' }}>Column Z</option>
      </select>            
    </div>

    <div class="col-lg-6 col-xl-3 col-12 formula-column-value-div" style="display:  none;">
      <label class="form-label" for="value">Value</label>    
      <input id="form-formula-repeater-0-3" name="value" class="form-control value" />
    </div> 

    <div class="col-lg-6 col-xl-3 col-12">               
      <button class="btn btn-label-danger mt-4" data-repeater-delete>
        <i class="bx bx-x me-1"></i>
        <span class="align-middle">Delete</span>
      </button>
    </div>         
  </div>

</div>

{{--
<div data-repeater-item>
  <div class="row">
    <div class="mb-3 col-lg-6 col-xl-3 col-12 mb-0">
      <label class="form-label" for="form-repeater-1-1">Username</label>
      <input type="text" id="form-repeater-1-1" class="form-control" placeholder="john.doe" />
    </div>
    <div class="mb-3 col-lg-6 col-xl-3 col-12 mb-0">
      <label class="form-label" for="form-repeater-1-2">Password</label>
      <input type="password" id="form-repeater-1-2" class="form-control" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
    </div>
    <div class="mb-3 col-lg-6 col-xl-2 col-12 mb-0">
      <label class="form-label" for="form-repeater-1-3">Gender</label>
      <select id="form-repeater-1-3" class="form-select">
        <option value="Male">Male</option>
        <option value="Female">Female</option>
      </select>
    </div>
    <div class="mb-3 col-lg-6 col-xl-2 col-12 mb-0">
      <label class="form-label" for="form-repeater-1-4">Profession</label>
      <select id="form-repeater-1-4" class="form-select">
        <option value="Designer">Designer</option>
        <option value="Developer">Developer</option>
        <option value="Tester">Tester</option>
        <option value="Manager">Manager</option>
      </select>
    </div>
    <div class="mb-3 col-lg-12 col-xl-2 col-12 d-flex align-items-center mb-0">
      <button class="btn btn-label-danger mt-4" data-repeater-delete>
        <i class="bx bx-x me-1"></i>
        <span class="align-middle">Delete</span>
      </button>
    </div>
  </div>
  <hr>
</div>--}}