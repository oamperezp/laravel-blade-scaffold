<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('{MODEL_TITLE_PLURAL}') }}
            </h2>
            <a href="{{ route('{MODEL_PLURAL}.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                {{ __('Create {MODEL_TITLE}') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    {TABLE_HEADERS}
                                    <th class="min-w-[100px]">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse(${MODEL_PLURAL} as ${MODEL_VARIABLE})
                                    <tr class="hover:bg-gray-50">
                                        {TABLE_COLUMNS}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center space-x-2">
                                                <a href="{{ route('{MODEL_PLURAL}.show', ${MODEL_VARIABLE}) }}" class="text-blue-600 hover:text-blue-900">
                                                    {{ __('View') }}
                                                </a>
                                                <a href="{{ route('{MODEL_PLURAL}.edit', ${MODEL_VARIABLE}) }}" class="text-indigo-600 hover:text-indigo-900">
                                                    {{ __('Edit') }}
                                                </a>
                                                <form action="{{ route('{MODEL_PLURAL}.destroy', ${MODEL_VARIABLE}) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Are you sure?') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                                        {{ __('Delete') }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="100" class="px-6 py-4 text-center text-gray-500">
                                            {{ __('No {MODEL_PLURAL} found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ ${MODEL_PLURAL}->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>