<!-- Large Modal -->
<div class="modal fade modal-file" id="formulaModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLabel">Formula</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">            
        <form class="form-formula-repeater">
          <input type="hidden" name="sheet_index" id="sheet_index" value="">
          <input type="hidden" name="column_index" id="column_index" value="">
{{--
        <form id="formFormula" class="needs-validation m-0 formFormula" novalidate>
          @csrf
          --}}
          <div class="row mb-2">
            <div class="col-lg-6 col-xl-4 col-12">
              <span id="mapped_column" class="fw-bold fs-5">{{ isset($header) ? $header : '' }}</span> (OR)
              <input id="initial_column_or_value" name="initial_column_or_value" class="form-control initial-column-or-value d-inline-block w-px-100" placeholder="100" />
            </div>
          </div>

          <div data-repeater-list="formula">
            @include('_partials/_modals/_anyexcel-formula-row-repeater')
          </div>

          <button class="btn btn-primary" data-repeater-create>
            <i class="bx bx-plus me-1"></i>
            <span class="align-middle">Add</span>
          </button>

{{--            
          <div class="row formula-row">
            <div class="col-lg-6 col-xl-3 col-12">
              <label class="form-label" for="formula_arithmetic">Arithmetic</label>
              <select id="formula_arithmetic" name="formula_arithmetic" class="form-select formula-arithmetic">
                <option value="">-- Select --</option>
                <option value="+">+</option>
                <option value="-">-</option>
                <option value="*">*</option>
                <option value="/">/</option>           
              </select>
            </div>
  

          <div class="col-lg-6 col-xl-3 col-12">  
            <label class="form-label" for="formula_column">Column</label>          
           
            <select id="formula_column" name="formula_column" class="form-select formula-column">
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
            <label class="form-label" for="formula_column_value">Value</label>    
            <input id="formula_column_value" name="formula_column_value" class="form-control formula-column-value" placeholder="100" />
          </div>          
        </div>

        <div class="row">
          <div class="col-lg-6 col-xl-3 col-12">               
            <!-- <button type="button" class="btn btn-primary btn-add-formula-row mt-4">+ Add</button> -->
            <button class="btn btn-primary" data-repeater-create>
              <i class="bx bx-plus me-1"></i>
              <span class="align-middle">Add</span>
            </button>
          </div>
        </div>
--}}
          <div class="row my-3">
            <div class="col-sm-12">   
              <span class="fw-bold text-decoration-underline d-block" for="end_formula">Formula:</span>
              <span id="end_formula"></span>
            </div>                                                      
          </div> 

          <div class="row">
            <div class="col-sm-12 text-end">   
              <button type="button" class="btn btn-danger ms-2 btn-remove-formula">Remove Formula</button>            
              <button type="button" class="btn btn-primary ms-2 btn-add-formula">Add Formula</button>
            </div>                                                      
          </div> 
          
        </form>  
      
      </div>      
    </div>
  </div>
</div>