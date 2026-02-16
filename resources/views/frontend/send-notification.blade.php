@extends('frontend.master')

@section('main-content')
<div class="py-4 ms-3 me-3">
    @include('frontend.body.admin-nav')

    <div class="row justify-content-center mt-3">
        <div class="col-lg-8">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Send Notification</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.notifications.send') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control" rows="4" required>{{ old('description') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Image (optional)</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <small class="text-muted">Supported: jpeg, png, jpg, gif, webp (max 2MB)</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Link (optional)</label>
                            <input type="text" name="link" class="form-control" placeholder="https://example.com or /some-path" value="{{ old('link') }}">
                            <small class="text-muted">When user clicks notification, they will be redirected to this link. Default is home page.</small>
                        </div>

                        <hr>

                        @php
                            $oldTarget = old('target_type', 'international');
                        @endphp

                        <div class="mb-3">
                            <label class="form-label d-block">Target Audience <span class="text-danger">*</span></label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="target_type" id="target_international" value="international" {{ $oldTarget === 'international' ? 'checked' : '' }}>
                                <label class="form-check-label" for="target_international">International (All Users)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="target_type" id="target_country" value="country" {{ $oldTarget === 'country' ? 'checked' : '' }}>
                                <label class="form-check-label" for="target_country">Country Only</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="target_type" id="target_city" value="city" {{ $oldTarget === 'city' ? 'checked' : '' }}>
                                <label class="form-check-label" for="target_city">Specific City</label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Country</label>
                                    <select name="country_id" id="country_select" class="form-select">
                                        <option value="">Select Country</option>
                                        @foreach($countries as $country)
                                            <option value="{{ $country->id }}" {{ old('country_id') == $country->id ? 'selected' : '' }}>
                                                {{ $country->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">City</label>
                                    <select name="city_id" id="city_select" class="form-select">
                                        <option value="">Select City</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                Send Notification
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const targetInternational = document.getElementById('target_international');
        const targetCountry = document.getElementById('target_country');
        const targetCity = document.getElementById('target_city');
        const countrySelect = document.getElementById('country_select');
        const citySelect = document.getElementById('city_select');

        function updateTargetInputs() {
            if (targetInternational.checked) {
                countrySelect.disabled = true;
                citySelect.disabled = true;
            } else if (targetCountry.checked) {
                countrySelect.disabled = false;
                citySelect.disabled = true;
            } else if (targetCity.checked) {
                countrySelect.disabled = false;
                citySelect.disabled = false;
            }
        }

        targetInternational.addEventListener('change', updateTargetInputs);
        targetCountry.addEventListener('change', updateTargetInputs);
        targetCity.addEventListener('change', updateTargetInputs);

        // Load cities when country changes
        countrySelect.addEventListener('change', function () {
            const countryId = this.value;
            if (!countryId) {
                citySelect.innerHTML = '<option value=\"\">Select City</option>';
                return;
            }

            fetch(`/get-cities/${countryId}`)
                .then(response => response.json())
                .then(cities => {
                    citySelect.innerHTML = '<option value=\"\">Select City</option>';
                    cities.forEach(city => {
                        const option = document.createElement('option');
                        option.value = city.id;
                        option.textContent = city.name;
                        citySelect.appendChild(option);
                    });

                    const oldCityId = '{{ old('city_id') }}';
                    if (oldCityId) {
                        citySelect.value = oldCityId;
                    }
                })
                .catch(error => {
                    console.error('Error loading cities:', error);
                });
        });

        // Initial state
        updateTargetInputs();

        // If old country exists, load its cities on page load
        const oldCountryId = '{{ old('country_id') }}';
        if (oldCountryId) {
            const event = new Event('change');
            countrySelect.dispatchEvent(event);
        }
    });
</script>
@endsection

