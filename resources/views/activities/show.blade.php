{{-- Activity Log Section --}}
<div class="mt-4">
    @include('activities._form', ['lead' => $lead, 'customer' => null])
    @include('activities._timeline', ['activities' => $lead->activities])
</div>

{{-- For customer show page, swap to: --}}
{{-- @include('activities._form', ['lead' => null, 'customer' => $customer]) --}}
{{-- @include('activities._timeline', ['activities' => $customer->activities]) --}}