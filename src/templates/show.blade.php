<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('View {MODEL_TITLE}') }}
            </h2>
            <div class="flex items-center space-x-2">
                <a href="{{ route('{MODEL_PLURAL}.edit', ${MODEL_VARIABLE}) }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Edit') }}
                </a>
                <a href="{{ route('{MODEL_PLURAL}.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    {{ __('Back to list') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="space-y-4">
                        {SHOW_FIELDS}
                        <div class="border-t pt-4 mt-4">
                            <x-label-group label="{{ __('Created at') }}"
                                description="{{ ${MODEL_VARIABLE}->created_at->format('d/m/Y H:i') }}" />
                            <x-label-group label="{{ __('Updated at') }}"
                                description="{{ ${MODEL_VARIABLE}->updated_at->format('d/m/Y H:i') }}" />
                        </div>

                        <div class="flex items-center justify-end space-x-2 mt-6">
                            <form action="{{ route('{MODEL_PLURAL}.destroy', ${MODEL_VARIABLE}) }}" method="POST"
                                onsubmit="return confirm('{{ __('Are you sure you want to delete this item?') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    {{ __('Delete') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>