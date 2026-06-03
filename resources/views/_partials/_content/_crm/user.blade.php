<div class="input-group mb-3">  
  <div class="form-floating">        
    <select id="user-role" class="form-select" name="role" required>
      <option value="company-admin" {{ ($user->roles->first()->name == 'company-admin') ? 'selected' : '' }}>Company Admin</option>
      <option value="team-user" {{ ($user->roles->first()->name == 'team-user') ? 'selected' : '' }}>Team User</option>    
      <option value="client-user" {{ ($user->roles->first()->name == 'team-user') ? 'selected' : '' }}>Client User</option>
    </select>
    <label class="form-label" for="user-role">Role</label> 
  </div>
</div>

<div class="input-group mb-3">
  <div class="form-floating">
    <input type="text" id="user-firstname" class="form-control" placeholder="John" aria-label="John" name="firstname" required value="{{ $user->dvuser->firstname }}" />
    <label class="form-label" for="user-firstname">First Name</label>
  </div>
</div>

<div class="input-group mb-3">
  <div class="form-floating">
    <input type="text" id="user-lastname" class="form-control" placeholder="John" aria-label="John" name="lastname" required value="{{ $user->dvuser->lastname }}" />
    <label class="form-label" for="user-lastname">Last Name</label>
  </div>
</div>

<div class="input-group mb-3">
  <div class="form-floating">
    <input type="email" id="user-email" class="form-control" placeholder="john.doe" aria-label="john.doe" name="email" required value="{{ $user->email }}" />
    <label class="form-label" for="user-email">Email</label>
  </div>
</div>

<div class="input-group mb-3">
  <div class="form-floating">
    <input type="text" id="user-telephone" class="form-control phone-mask telephone" placeholder="00 00 00 00" aria-label="00 00 00 00" name="telephone" required value="{{ $user->dvuser->telephone }}"/>
    <label class="form-label" for="user-telephone">Telephone</label>
  </div>
</div> 

<div class="input-group mb-3">
  <div class="form-floating">
    <input type="text" id="user-designation" class="form-control" placeholder="CEO" aria-label="CEO" name="designation" value="{{ $user->dvuser->designation }}"/>
    <label class="form-label" for="user-designation">Title</label>
  </div>
</div>      

<div class="input-group mb-3">
  <div class="form-floating">
    <select id="user-lang" class="form-select" name="lang" required>          
      <option value="en" {{ ($user->dvuser->lang == 'en') ? 'selected' : '' }}>English</option>
      <option value="dk" {{ ($user->dvuser->lang == 'dk') ? 'selected' : '' }}>Danish</option>
    </select>
    <label class="form-label" for="user-lang">Language</label>
  </div>
</div>