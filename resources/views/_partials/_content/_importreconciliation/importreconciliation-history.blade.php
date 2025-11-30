<!-- Timeline Advanced-->
@php
  $timelines = $histories['timelines'];
  $timelinefiles = $histories['timelinefiles'];
  $timelineClass = 'timeline-item-';
@endphp
<div class="row overflow-hidden">
  <div class="col-12">
    <ul class="timeline timeline-center mt-5">
      @foreach ($timelines as $timelinekey => $timeline)      
        @if($timeline['created_at'])
          <li class="timeline-item timeline-item-{{ ($timeline['role'] == 'team-user') ? 'success' : $timeline['color'] }} mb-md-4 mb-5 {{$timelineClass . $timeline['direction']}}">
            <span class="timeline-indicator timeline-indicator-success border-0" data-aos="zoom-in" data-aos-delay="200">
              <!-- <i class="bx bx-receipt"></i> -->
            </span>
            <div class="timeline-event card p-0" data-aos="fade-left">
              <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <div class="d-flex flex-wrap">
                  <div class="avatar me-3">
                    <img src="{{ $timeline['profile_photo_url'] ? $timeline['profile_photo_url'] : asset('assets/img/avatars/1.png') }}" alt="Avatar" class="rounded-circle" />
                  </div>
                  <div>
                    <p class="mb-0">{{ ($timeline['firstname'] == "Super" && $timeline['lastname'] == "Admin") ? "System" : ($timeline['firstname'] . ' ' . $timeline['lastname']) }}</p>
                    <span class="text-muted">{{ \Carbon\Carbon::parse($timeline['created_at'])->format('d-m-Y H:i') }}</span>
                  </div>  
                </div>
                <div class="d-flex align-items-center meta">
                  <h6 class="mb-0">{{ isset($timeline['reminder_action_name']) ? 'Reminder ' : '' }}{{ $timeline['subject'] }}</h6>
                  @if(isset($timeline['filetype']))
                    @if($timeline['filetype'] == 'importvatcomments')
                      <!-- Edit/Delete -->
                      <div class="dropdown ms-3">                     
                        <i class="bx bx-dots-vertical-rounded cursor-pointer fs-4" id="dropdownEditDelete" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownEditDelete">
                          <a class="dropdown-item btn-edit-import-vat-comment" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#onboardingSlideOverwriteImportVatCommentModal-{{ $vat_reg_id }}-{{ $timeline['fileid'] }}-{{ $timeline['lineno'] }}" title="Edit Comment">
                            <i class="bx bx-pencil bx-xs me-1"></i>
                            <span class="align-middle">Edit</span>
                          </a>
                          <a class="dropdown-item btn-delete-import-vat-comment" href="javascript:void(0)" data-client_id="{{ $client_id }}" data-vat_reg_id="{{ $vat_reg_id }}"  data-import_vat_file_id="{{ $timeline['fileid'] }}" data-import_vat_comment_line_no="{{ $timeline['lineno'] }}" data-import_vat_comment_id="{{ $timeline['comment_id'] }}" title="Delete Comment">                          
                            <i class="bx bx-trash bx-xs me-1"></i>
                            <span class="align-middle">Trash</span>
                          </a>
                        </div>
                      </div>                      
                      <!--/ Edit/Delete -->
                    @endif    
                  @endif    
                </div>
              </div>     
              <hr class="my-0 mx-4 {{ (isset($timeline['filetype'])) ? (($timeline['filetype'] == 'importvatfiles' || $timeline['filetype'] == 'importreconciliationcominvoices' || $timeline['filetype'] == 'importreconciliationsalesinvoices') ? 'border-bottom' : '') : '' }}">     
              <div class="card-body">   
                {!! isset($timeline['reminder_action_name']) ? '<p class="mb-2">' . $timeline['reminder_action_name'] . '</p>' : '' !!}
                <p class="mb-2">{!! $timeline['message'] !!} {{ (isset($timeline['filetype'])) ? (($timeline['filetype'] == 'importvatfiles' || $timeline['filetype'] == 'importreconciliationcominvoices' || $timeline['filetype'] == 'importreconciliationsalesinvoices') ?  (($timeline['monthyear'] != '') ? \Carbon\Carbon::parse('01-'.$timeline['monthyear'])->format('F Y') : '') : '') : '' }}</p>  

                @if(isset($timeline['filetype']))
                  @if($timeline['filetype'] == 'importreconciliationcominvoices' || $timeline['filetype'] == 'importreconciliationsalesinvoices')                    
                  @else
                    @foreach ($timelinefiles as $filekey => $timelinefile)
                      @if(($timeline['filetype'] == $timelinefile['filetype']) && ($timeline['fileid'] == $timelinefile['fileid']))                        
                        <span class="btn-download-files cursor-pointer" data-fileid="{{ $timelinefile['id'] }}"><i class="bx bx-paperclip cursor-pointer"></i> {{ $timelinefile['file_name'] }}</span><br/>
                      @endif
                    @endforeach
                  @endif  
                @endif
              </div>
              <div class="timeline-event-time"></div>
            </div>
          </li>

          @if(isset($timeline['filetype']))
            @if($timeline['filetype'] == 'importvatcomments')
              @include('_partials/_modals/modal-overwrite-import-vat-comment-lazy')  
            @endif
          @endif

        @endif
      @endforeach
    </ul>
  </div>
</div>
<!-- /Timeline Advanced-->