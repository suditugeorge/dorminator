<form>
    <!--Second row-->
    <div class="row">
        <!--First column-->
        <div class="col-md-6">
            <div class="md-form">
                <input type="email" id="email" class="form-control" disabled value="{{$user->email}}">
                <label for="email" class="disabled">Adresă email</label>
            </div>
        </div>
        <!--Second column-->
        @if($user->is_super_admin || $user->is_admin)
            <div class="col-md-6">
                <div class="md-form">
                    <input type="text" id="name" class="form-control validate" value="{{$user->contact->first_name}}">
                    <label for="name">Nume</label>
                </div>
            </div>
        @else
            <div class="col-md-6">
                    <div class="md-form">
                        <input type="text" id="name" class="form-control validate" value="{{$user->contact->first_name}}" disabled>
                        <label for="name" class="disabled">Nume</label>
                    </div>
            </div>
        @endif
    </div>
    <!--/.Second row-->
    <!--Second row-->
    <div class="row">
        <!--First column-->
        <div class="col-md-3">
            <div class="md-form">
                <input type="text" id="created_at" class="form-control" disabled value="{{$user->created_at->format('d-m-Y')}}">
                <label for="created_at" class="disabled">Cont creat în</label>
            </div>
        </div>
        <!--Second column-->
        <div class="col-md-3">
            <div class="md-form">
                <input type="text" id="updated_at" class="form-control" disabled value="{{$user->updated_at->format('d-m-Y')}}">
                <label for="updated_at">Modificat la</label>
            </div>
        </div>
        <div class="col-md-6">
            <div class="md-form">
                <input type="text" id="phone" class="form-control" disabled value="{{$user->phone}}">
                <label for="phone">Telefon</label>
            </div>
        </div>
    </div>
    <!--/.Second row-->

    <div class="row">
        @if($user->is_super_admin || $user->is_admin)
            <div class="col-md-6">
                <select class="mdb-select" id="degree">
                    @if($user->degree == "Licentă")
                        <option value="Licentă" selected="selected">Licentă</option>
                    @else
                        <option value="Licentă">Licentă</option>
                    @endif
                    @if($user->degree == "Masterat")
                        <option value="Masterat" selected="selected">Masterat</option>
                    @else
                        <option value="Masterat">Masterat</option>
                    @endif
                    @if($user->degree == "Doctorat")
                        <option value="Doctorat" selected="selected">Doctorat</option>
                    @else
                        <option value="Doctorat">Doctorat</option>
                    @endif
                    @if($user->degree == "Admin")
                        <option value="Admin" selected="selected">Admin</option>
                    @else
                        <option value="Admin">Admin</option>
                    @endif                      
                </select>
                <label>Program de studiu</label>
            </div>
            <div class="col-md-6">
                <select class="mdb-select" id="year-of-study">
                    @if($user->year_of_study == 1)
                        <option value="1" selected="selected">I</option>
                    @else
                        <option value="1">I</option>
                    @endif
                    @if($user->year_of_study == 2)
                        <option value="2" selected="selected">II</option>
                    @else
                        <option value="2">II</option>
                    @endif
                    @if($user->year_of_study == 3)
                        <option value="3" selected="selected">III</option>
                    @else
                        <option value="3">III</option>
                    @endif
                    @if($user->year_of_study == 4)
                        <option value="4" selected="selected">IV</option>
                    @else
                        <option value="4">IV</option>
                    @endif                      
                </select>
                <label>An de studiu</label>
            </div>
        @else
            <div class="col-md-6">
                <select class="mdb-select" disabled id="degree">
                    @if($user->degree == "Licentă")
                        <option value="Licentă" selected="selected" disabled>Licentă</option>
                    @else
                        <option value="Licentă">Licentă</option>
                    @endif
                    @if($user->degree == "Masterat")
                        <option value="Masterat" selected="selected" disabled>Masterat</option>
                    @else
                        <option value="Masterat">Masterat</option>
                    @endif
                    @if($user->degree == "Doctorat")
                        <option value="Doctorat" selected="selected" disabled>Doctorat</option>
                    @else
                        <option value="Doctorat">Doctorat</option>
                    @endif                     
                </select>
                <label>Program de studiu</label>
            </div>
            <div class="col-md-6">
                <select class="mdb-select" disabled id="year-of-study">
                    @if($user->year_of_study == 1)
                        <option value="1" selected="selected" disabled>I</option>
                    @else
                        <option value="1">I</option>
                    @endif
                    @if($user->year_of_study == 2)
                        <option value="2" selected="selected" disabled>II</option>
                    @else
                        <option value="2">II</option>
                    @endif
                    @if($user->year_of_study == 3)
                        <option value="3" selected="selected" disabled>III</option>
                    @else
                        <option value="3">III</option>
                    @endif
                    @if($user->year_of_study == 4)
                        <option value="4" selected="selected" disabled>IV</option>
                    @else
                        <option value="4">4</option>
                    @endif                      
                </select>
                <label>An de studiu</label>
            </div>
        @endif
    </div>
    <div class="row">
        @if($user->is_super_admin || $user->is_admin)
            @if($user->gender == "Male")
                <div class="col-md-6">
                <fieldset class="form-group">
                    <input name="gender" type="radio" id="Male" checked="checked">
                    <label for="Male">Sex M.</label>
                </fieldset>
                </div>
                <div class="col-md-6">
                <fieldset class="form-group">
                    <input name="gender" type="radio" id="Female">
                    <label for="Female">Sex F.</label>
                </fieldset>
                </div>
            @else
                <div class="col-md-6">
                    <fieldset class="form-group">
                        <input name="gender" type="radio" id="Male">
                        <label for="Male">Sex M.</label>
                    </fieldset>
                    </div>
                    <div class="col-md-6">
                    <fieldset class="form-group">
                        <input name="gender" type="radio" id="Female" checked="checked">
                        <label for="Female">Sex F.</label>
                    </fieldset>
                </div>
            @endif
        @else
            @if($user->gender == "Male")
                <div class="col-md-6">
                <fieldset class="form-group">
                    <input name="gender" type="radio" id="Male" checked="checked" disabled>
                    <label for="Male" class="disabled">Sex M.</label>
                </fieldset>
                </div>
                <div class="col-md-6">
                <fieldset class="form-group">
                    <input name="gender" type="radio" id="Female" disabled>
                    <label for="Female" class="disabled">Sex F.</label>
                </fieldset>
                </div>
            @else
                <div class="col-md-6">
                    <fieldset class="form-group">
                        <input name="gender" type="radio" id="Male" disabled>
                        <label for="Male" class="disabled">Sex M.</label>
                    </fieldset>
                    </div>
                    <div class="col-md-6">
                    <fieldset class="form-group">
                        <input name="gender" type="radio" id="Female" checked="checked" disabled>
                        <label for="Female" class="disabled">Sex F.</label>
                    </fieldset>
                </div>
            @endif 
        @endif                                                           
    </div>

    @if($user->is_super_admin || $user->is_admin)
    <!-- Fourth row -->
    <div class="row">
        <div class="col-md-12 text-center">
            <button class="btn btn-primary" id="change-user-profile">Schimbă datele</button>
        </div>
    </div>
    <!-- /.Fourth row -->
    @endif

  <div class="row danger-color hidden" id="error-box-profile">
    <div class="col-md-12 text-center">
    <i class="fa fa-warning fa-3x mt-1"></i><p class="text-center font-weight-bold" id="error-message-profile">Unul sau mai multe câmpuri marcate cu roșu conțin erori</p></div></div>
  </div>

</form>
<!-- Edit Form -->