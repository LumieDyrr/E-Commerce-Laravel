<!-- resources/views/vendor/pagination/custom.blade.php -->

<div class="px-4 py-2 border-gray-200 rounded-b-lg">
  <ol class="flex justify-end gap-1 text-xs font-medium">
    <!-- Previous Button -->
    @if ($item->onFirstPage())
      <li>
        <span class="inline-flex items-center justify-center text-gray-400 bg-white border border-gray-100 rounded cursor-not-allowed size-8">
          <span class="sr-only">Prev Page</span>
          <svg xmlns="http://www.w3.org/2000/svg" class="size-3" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
          </svg>
        </span>
      </li>
    @else
      <li>
        <a href="{{ $item->previousPageUrl() }}" class="inline-flex items-center justify-center text-gray-900 bg-white border border-gray-100 rounded size-8">
          <span class="sr-only">Prev Page</span>
          <svg xmlns="http://www.w3.org/2000/svg" class="size-3" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
          </svg>
        </a>
      </li>
    @endif

    <!-- Page Numbers -->
    @foreach ($item->getUrlRange(1, $item->lastPage()) as $page => $url)
      <li>
        @if ($page == $item->currentPage())
          <span class="block leading-8 text-center text-white bg-blue-600 border-blue-600 rounded size-8">{{ $page }}</span>
        @else
          <a href="{{ $url }}" class="block leading-8 text-center text-gray-900 bg-white border border-gray-100 rounded size-8">{{ $page }}</a>
        @endif
      </li>
    @endforeach

    <!-- Next Button -->
    @if ($item->hasMorePages())
      <li>
        <a href="{{ $item->nextPageUrl() }}" class="inline-flex items-center justify-center text-gray-900 bg-white border border-gray-100 rounded size-8">
          <span class="sr-only">Next Page</span>
          <svg xmlns="http://www.w3.org/2000/svg" class="size-3" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
          </svg>
        </a>
      </li>
    @else
      <li>
        <span class="inline-flex items-center justify-center text-gray-400 bg-white border border-gray-100 rounded cursor-not-allowed size-8">
          <span class="sr-only">Next Page</span>
          <svg xmlns="http://www.w3.org/2000/svg" class="size-3" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
          </svg>
        </span>
      </li>
    @endif
  </ol>
</div>
