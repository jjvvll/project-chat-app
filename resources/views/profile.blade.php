<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <livewire:profile.update-profile-information-form />
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <livewire:profile.update-password-form />
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <livewire:profile.delete-user-form />
                </div>
            </div>
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <form action="{{ route('users.addProfilePicture', ['userId' => auth()->user()->id]) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf

                    <!-- Current Profile Picture Display -->
                    <div class="mb-4">
                        <label class="block font-medium mb-2">Current Profile Picture</label>
                        @if (auth()->user()->profile_photo)
                            <img src="{{ asset('storage/' . auth()->user()->profile_photo) }}" alt="Profile Picture"
                                class="w-20 h-20 rounded-full object-cover border-2 border-gray-300">
                        @else
                            <div
                                class="w-20 h-20 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                <span class="text-xs text-gray-500 dark:text-gray-400">No photo</span>
                            </div>
                        @endif
                    </div>

                    <!-- Upload New Picture -->
                    <div class="mb-4">
                        <label for="profilePhoto" class="block font-medium">Upload new profile picture</label>
                        <input type="file" name="profilePhoto" id="profilePhoto" accept="image/*" class="block mt-1">
                    </div>

                    <!-- Preview New Upload (Optional) -->
                    <div class="mb-4" id="preview-container" style="display: none;">
                        <label class="block font-medium mb-2">Preview</label>
                        <img id="preview-image" src="" alt="Preview"
                            class="w-20 h-20 rounded-full object-cover border-2 border-blue-500">
                    </div>

                    <button type="submit"
                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Upload</button>
                </form>
            </div>

            <!-- JavaScript for live preview -->
            <script>
                document.getElementById('profilePhoto').addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            document.getElementById('preview-image').src = e.target.result;
                            document.getElementById('preview-container').style.display = 'block';
                        }
                        reader.readAsDataURL(file);
                    }
                });
            </script>
        </div>
    </div>


</x-app-layout>
