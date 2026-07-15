@php
    /**
     * Expected variables:
     * - $name (string) input name, e.g. akses[0][kode_project]
     * - $projects (Collection)
     * - $selectedCsv (string|null) optional selected kode_project values (single or CSV)
     */
    $selectedCsv = $selectedCsv ?? null;

    if (is_array($selectedCsv)) {
        $selectedArray = array_map('strval', $selectedCsv);
    } else {
        $selectedCsvStr = (string) $selectedCsv;
        if ($selectedCsvStr === '' || $selectedCsvStr === 'all') {
            $selectedArray = [];
        } else {
            $selectedArray = array_filter(array_map('trim', explode(',', $selectedCsvStr)));
        }
    }

    $isAllSelected = $selectedCsv === 'all' || $selectedCsv === '' || $selectedCsv === null;
@endphp

<div class="project-multiselect-wrapper" data-initialized="false">
    <input type="hidden" name="{{ $name }}" value="{{ $isAllSelected ? 'all' : implode(',', $selectedArray) }}" class="project-value-input">
    
    <div class="dropdown w-100">
        <button class="btn btn-sm btn-outline-secondary dropdown-toggle w-100 text-start project-dropdown-btn" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport" aria-expanded="false">
            <span class="selected-text">
                @if($isAllSelected)
                    ALL Project
                @else
                    {{ count($selectedArray) }} Project dipilih
                @endif
            </span>
        </button>
        <ul class="dropdown-menu w-100 project-dropdown-menu" data-bs-popper="fixed" style="max-height: 300px; overflow-y: auto;">
            <li>
                <label class="dropdown-item">
                    <input type="checkbox" class="form-check-input me-2 project-checkbox-all" value="all" {{ $isAllSelected ? 'checked' : '' }}>
                    <strong>ALL Project</strong>
                </label>
            </li>
            <li><hr class="dropdown-divider"></li>
            @foreach($projects as $p)
                <li>
                    <label class="dropdown-item">
                        <input type="checkbox" class="form-check-input me-2 project-checkbox-item" value="{{ $p->kode_project }}" 
                            {{ in_array((string)$p->kode_project, $selectedArray) ? 'checked' : '' }}>
                        {{ $p->nama_project ?? $p->kode_project }} ({{ $p->kode_project }})
                    </label>
                </li>
            @endforeach
        </ul>
    </div>
</div>

@once
<style>
    .project-multiselect-wrapper .project-dropdown-menu {
        min-width: 320px;
    }
    .project-multiselect-wrapper .project-dropdown-btn {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .project-multiselect-wrapper .dropdown-item {
        white-space: normal;
        display: flex;
        align-items: flex-start;
        gap: 8px;
        padding: 6px 12px;
        cursor: pointer;
    }
    .project-multiselect-wrapper .dropdown-item input[type="checkbox"] {
        margin-top: 3px;
        flex-shrink: 0;
    }
</style>
<script>
(function() {
    function initProjectMultiselect(wrapper) {
        if (wrapper.getAttribute('data-initialized') === 'true') return;
        wrapper.setAttribute('data-initialized', 'true');

        const valueInput = wrapper.querySelector('.project-value-input');
        const selectedText = wrapper.querySelector('.selected-text');
        const allCheckbox = wrapper.querySelector('.project-checkbox-all');
        const projectCheckboxes = wrapper.querySelectorAll('.project-checkbox-item');

        function updateValue() {
            if (allCheckbox.checked) {
                valueInput.value = 'all';
                selectedText.textContent = 'ALL Project';
                projectCheckboxes.forEach(cb => {
                    cb.checked = false;
                });
            } else {
                const selected = Array.from(projectCheckboxes).filter(cb => cb.checked).map(cb => cb.value);
                if (selected.length === 0) {
                    valueInput.value = 'all';
                    selectedText.textContent = 'ALL Project';
                    allCheckbox.checked = true;
                } else {
                    valueInput.value = selected.join(',');
                    selectedText.textContent = selected.length + ' Project dipilih';
                }
            }
        }

        allCheckbox.addEventListener('change', function() {
            updateValue();
        });

        projectCheckboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                if (this.checked) {
                    allCheckbox.checked = false;
                }
                updateValue();
            });
        });

        // Prevent dropdown from closing when clicking inside the menu
        const dropdownMenu = wrapper.querySelector('.project-dropdown-menu');
        if (dropdownMenu) {
            dropdownMenu.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.project-multiselect-wrapper').forEach(initProjectMultiselect);
        });
    } else {
        document.querySelectorAll('.project-multiselect-wrapper').forEach(initProjectMultiselect);
    }

    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === 1) {
                    if (node.classList && node.classList.contains('project-multiselect-wrapper')) {
                        initProjectMultiselect(node);
                    }
                    node.querySelectorAll && node.querySelectorAll('.project-multiselect-wrapper').forEach(initProjectMultiselect);
                }
            });
        });
    });

    observer.observe(document.body, { childList: true, subtree: true });
})();
</script>
@endonce
