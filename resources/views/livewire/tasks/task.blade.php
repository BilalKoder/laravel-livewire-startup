<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Task
    </h2>
</x-slot>
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg px-4 py-4">
            <div class="grid gap-4">
                <div class="font-bold text-xl mb-2">{{ $task->title??'' }}</div>
                <div class="flex">
                    by&nbsp;<span class="italic">{{ !empty($task->user) ? $task->user->first_name : ''}} {{ !empty($task->user)? $task->user->last_name:''}}</span>
                    &nbsp;in&nbsp;
                   {{-- <a href="{{ url('dashboard/categories/' . $task->category->id . '/tasks') }}" --}}
                   <a href="javascript:void(0)"     
                   class="underline">{{ !empty($task->category)? $task->category->title:'' }}</a>
                    &nbsp;on&nbsp;{{ !empty($task->updated_at) ? $task->updated_at->format('F, d Y'):'' }}
                </div>
            
                <div class="text-gray-700 text-base">
                    {!! $task->description ??'' !!}
                </div>   
                @if (!empty($task->progress) &&$task->progress->count())
                    <div class="text-base">
                        <p class="text-gray-900 pt-2 pb-4">
                              Responses
                        </p>
                        <div class="bg-gray-100 overflow-hidden shadow-xl px-6 pt-4">
                            @if(!empty($task->progress))
                            @foreach ($task->progress as $progress_list)
                                <div>
                                    <p class="text-gray-500 font-bold">
                                        {{ !empty($progress_list->user) ? $progress_list->user->first_name:''  }} {{ !empty($progress_list->user)?  $progress_list->user->last_name:''}}</p>
                                    <p class="text-gray-400 text-xs">{{ $progress_list->created_at->format('F, d Y g:i a') }}
                                    </p>
                                    <p class="text-gray-500 pb-4">{{ $progress_list->progress_list }}</p>
                                </div>
                            @endforeach
                            @endif
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>
{{-- https://www.php.net/manual/en/datetime.format.php --}}
