<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      {{ $trip->title }}
    </h2>
  </x-slot>

  <div class="py-12 w-full">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col h-full">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 flex justify-between bg-white border-b border-gray-200">
          <div id="user-list">
            <!-- TODO: make this more interactive, alpine it -->
            @if ($trip->users->count() > 1)
            {{ __('Trip participants') }}:
            @foreach ($trip->users as $user)
            @if ($user->is(auth()->user()))
            @if ($loop->last)
            {{ $user->name }}
            @else
            {{ $user->name }},
            @endif
            @else
            @if ($loop->last)
            <div class="group inline">{{ $user->name }}
              <form class="inline" action="{{ route('trip-users.destroy', [$trip, $user]) }}" method="POST">@method('DELETE') @csrf <button class="hidden group-hover:inline-flex items-center justify-center rounded-full bg-white text-red-500 border-2 border-red-500 w-6 h-6 p-2 font-bold" id="remove-user-{{ $user->id }}" type="submit">&times;</button></form>
            </div>
            @else
            <div class="group inline">{{ $user->name }}
              <form class="inline" action="{{ route('trip-users.destroy', [$trip, $user]) }}" method="POST">@method('DELETE') @csrf <button class="hidden group-hover:inline-flex items-center justify-center rounded-full bg-white text-red-500 border-2 border-red-500 w-6 h-6 p-2 font-bold" id="remove-user-{{ $user->id }}" type="submit">&times;</button></form>,
            </div>
            @endif
            @endif
            @endforeach
            @else
            {{ __('You are the only participant') }}
            @endif
          </div>
          @unless ($users->isEmpty())
          <form action="{{ route('trip-users.store', $trip) }}" method="POST" class="flex">
            @csrf
            <select name="user_id" id="user" class="rounded-l-md px-8 py-2 rounded-r-none">
              <option value="">-- {{ __('add user') }} --</option>
              @foreach ($users as $user)
              @unless(auth()->user()->is($user))
              <option value="{{ $user->id }}">{{ $user->name }}</option>
              @endunless
              @endforeach
            </select>
            <x-primary-button class="rounded-l-none -ml-px" id="add-user">
              {{ __('Add') }}
            </x-primary-button>
          </form>
          @endunless
        </div>
      </div>
      <div x-data="{
        trip: {{ $trip }},
        showForm: false,
        newRegion: '',
      }" class="mt-6 flex flex-1 bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 flex flex-wrap w-full bg-white border-b border-gray-200">
          <div id="regions" class="w-full lg:w-1/5 xl:w-1/6 pr-6">
            <template x-for="region in trip.regions">
              <div cy-id="region-row" class="py-2 gap-3 group">
                <!-- TODO: delete icon inside div around p.w-full -->
                <p class="w-full"
                  @blur="e => $store.region.update(
                    '{{ route('trips.regions.store', $trip) }}/'+region.id,
                    '{{ csrf_token() }}',
                    { title: e.target.innerText}
                  ).then(data => trip.regions = data)"
                  contenteditable="true"
                  @input="(e) => region.title = e.target.innerText"
                  x-bind:id="`region-title-${region.id}`"
                  x-text="region.title"></p>
                <input class="w-full text-xs rounded-lg" x-bind:id="'arrival-at-' + region.id" type="datetime-local" step="1" name="arrival_at" x-bind:value="region.arrival_at" x-on:change="e => $store.region.update(
                    '{{ route('trips.regions.store', $trip) }}/'+region.id,
                    '{{ csrf_token() }}',
                    {arrival_at: e.target.value}
                  ).then(data => trip.regions = data)">
                <form x-bind:action="'{{ route('trips.regions.store', $trip) }}/'+region.id" method="post">
                  @csrf
                  @method('DELETE')
                  <button class="hidden group-hover:inline-flex items-center justify-center rounded-full bg-white text-red-500 border-2 border-red-500 w-6 h-6 p-2 font-bold">&times;</button>
                </form>
                <button class="items-center border-2 px-4 py-1 rounded-lg">{{ __("add place") }}</button>
              </div>
            </template>
            <template x-if="! showForm">
              <x-primary-button x-on:click="showForm = true" id="add-region">add region</x-primary-button>
            </template>
            <template x-if="showForm">
              <!-- TODO: adjust width -->
              <div class="w-full">
                <div>
                  <x-input-label for="region-title" :value="__('Region')" />
                  <x-text-input autofocus x-init="$watch('newRegion', title => $store.search.region(title))" class="mt-1 w-full" type="text" id="region-title" x-model="newRegion">
                  </x-text-input>
                </div>
                <div id="region-result" class="flex flex-wrap gap-3 mt-3">
                  <template x-for="region in $store.search.results.regions">
                    <button class="rounded-full bg-green-300 text-xs px-2 py-1" x-bind:title="region.display_name" x-text="region.display_name.split(', ')[0]" @click="$store.region.create(
                        '{{ route('trips.regions.store', $trip) }}',
                        '{{ csrf_token() }}',
                        region
                      ).then(data => {
                        trip.regions = data
                        newRegion = ''
                        showForm = !showForm
                      })"></button>
                  </template>
                </div>
              </div>
            </template>
          </div>
          <div class="w-full lg:w-4/5 xl:w-5/6">
            <div id="map" class="w-full h-full" x-init="$watch('trip', (trip => $store.map.update(trip))); $nextTick(() => $store.map.show(trip));"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>
