<!-- Delete Account Section -->
<section class="">
    <div class="mb-3">
        <h2 class="h5 text-danger">Delete Account</h2>
        <p class="text-muted">
            Once your account is deleted, all of its resources and data will be permanently deleted. 
            Before deleting your account, please download any data or information that you wish to retain.
        </p>
    </div>

    <!-- Delete Button trigger modal -->
    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
        Delete Account
    </button>



    <!-- Modal -->
    <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form method="POST" action="{{ route('profile.destroy') }}">
            @csrf
            @method('DELETE')
            <div class="modal-header">
              <h5 class="modal-title text-danger" id="deleteAccountModalLabel">Are you sure you want to delete your account?</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <p class="text-muted">
                Once your account is deleted, all of its resources and data will be permanently deleted. 
                Please enter your password to confirm you would like to permanently delete your account.
              </p>

              <!-- Password Input -->
              <div class="mb-3">
                  <label for="password" class="form-label visually-hidden">Password</label>
                  <input type="password" id="password" name="password" class="form-control" placeholder="Password">
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-danger">Delete Account</button>
            </div>
          </form>
        </div>
      </div>
    </div>
</section>