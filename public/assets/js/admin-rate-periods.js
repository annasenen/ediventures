document.addEventListener(
    'DOMContentLoaded',
    function () {

        'use strict';


        /*
        |--------------------------------------------------------------------------
        | ELEMENTS
        |--------------------------------------------------------------------------
        */

        const periodList =
            document.getElementById(
                'ratePeriodList'
            );

        const emptyState =
            document.getElementById(
                'ratePeriodEmptyState'
            );

        const addButton =
            document.getElementById(
                'addRatePeriodButton'
            );

        const drawerElement =
            document.getElementById(
                'ratePeriodDrawer'
            );

        const drawerTitle =
            document.getElementById(
                'ratePeriodDrawerTitle'
            );

        const periodForm =
            document.getElementById(
                'ratePeriodForm'
            );

        const periodIDField =
            document.getElementById(
                'pricingPeriodID'
            );

        const periodNameField =
            document.getElementById(
                'periodName'
            );

        const startTimeField =
            document.getElementById(
                'periodStartTime'
            );

        const endTimeField =
            document.getElementById(
                'periodEndTime'
            );

        const activeField =
            document.getElementById(
                'ratePeriodActive'
            );

        const dayFields =
            Array.from(
                document.querySelectorAll(
                    '.js-rate-period-day'
                )
            );

        const daysError =
            document.getElementById(
                'ratePeriodDaysError'
            );

        const saveButton =
            document.getElementById(
                'saveRatePeriodButton'
            );

        const ajaxMessage =
            document.getElementById(
                'ratePeriodAjaxMessage'
            );

        const toastElement =
            document.getElementById(
                'ratePeriodToast'
            );

        const toastMessage =
            document.getElementById(
                'ratePeriodToastMessage'
            );

        const coverageIcon =
            document.getElementById(
                'ratePeriodCoverageIcon'
            );

        const coverageTitle =
            document.getElementById(
                'ratePeriodCoverageTitle'
            );

        const coverageSummary =
            document.getElementById(
                'ratePeriodCoverageSummary'
            );

        const coverageGaps =
            document.getElementById(
                'ratePeriodCoverageGaps'
            );

        const coverageGapList =
            document.getElementById(
                'ratePeriodCoverageGapList'
            );


        if (
            !periodList ||
            !drawerElement ||
            !periodForm ||
            !addButton
        ) {
            return;
        }


        const drawer =
            bootstrap.Offcanvas
                .getOrCreateInstance(
                    drawerElement
                );


        /*
        |--------------------------------------------------------------------------
        | CSRF
        |--------------------------------------------------------------------------
        */

        function getCsrfToken()
        {
            const tokenField =
                periodForm.querySelector(
                    'input[name="csrf_token"]'
                );

            return tokenField
                ? tokenField.value
                : '';
        }


        /*
        |--------------------------------------------------------------------------
        | AJAX REQUEST
        |--------------------------------------------------------------------------
        */

        async function ratePeriodRequest(
            values
        ) {

            const formData =
                new FormData();

            formData.append(
                'csrf_token',
                getCsrfToken()
            );


            Object.entries(
                values
            ).forEach(
                function ([key, value]) {

                    if (
                        key === 'days' &&
                        Array.isArray(value)
                    ) {

                        value.forEach(
                            function (day) {

                                formData.append(
                                    'days[]',
                                    day
                                );
                            }
                        );

                        return;
                    }


                    formData.append(
                        key,
                        value
                    );
                }
            );


            const response =
                await fetch(
                    '/admin/ajax/rate-periods.php',
                    {
                        method:
                            'POST',

                        body:
                            formData,

                        headers: {
                            'X-Requested-With':
                                'XMLHttpRequest'
                        },

                        credentials:
                            'same-origin'
                    }
                );


            let result;

            try {

                result =
                    await response.json();

            } catch (error) {

                throw new Error(
                    'The server returned an invalid response.'
                );
            }


            if (
                !response.ok ||
                !result.success
            ) {

                throw new Error(
                    result.message ||
                    'The rate period change could not be saved.'
                );
            }


            return result;
        }


        /*
        |--------------------------------------------------------------------------
        | MESSAGES
        |--------------------------------------------------------------------------
        */

        function hideDrawerMessage()
        {
            if (!ajaxMessage) {
                return;
            }

            ajaxMessage.classList.add(
                'd-none'
            );

            ajaxMessage.classList.remove(
                'alert-success',
                'alert-danger'
            );

            ajaxMessage.textContent =
                '';
        }


        function showDrawerMessage(
            message,
            type = 'success'
        ) {

            if (!ajaxMessage) {
                return;
            }

            ajaxMessage.textContent =
                message;

            ajaxMessage.classList.remove(
                'd-none',
                'alert-success',
                'alert-danger'
            );

            ajaxMessage.classList.add(
                type === 'success'
                    ? 'alert-success'
                    : 'alert-danger'
            );
        }


        function showToast(
            message,
            type = 'success'
        ) {

            if (
                !toastElement ||
                !toastMessage
            ) {
                return;
            }

            toastMessage.textContent =
                message;

            toastElement.classList.remove(
                'text-bg-success',
                'text-bg-danger'
            );

            toastElement.classList.add(
                type === 'success'
                    ? 'text-bg-success'
                    : 'text-bg-danger'
            );

            bootstrap.Toast
                .getOrCreateInstance(
                    toastElement,
                    {
                        delay: 2500
                    }
                )
                .show();
        }


        /*
        |--------------------------------------------------------------------------
        | SAFE TEXT
        |--------------------------------------------------------------------------
        */

        function setText(
            element,
            value
        ) {

            if (!element) {
                return;
            }

            element.textContent =
                value ?? '';
        }

        /*
        |--------------------------------------------------------------------------
        | WEEKLY COVERAGE
        |--------------------------------------------------------------------------
        */

        function updateCoverage(
            coverage
        ) {

            if (
                !coverage ||
                !coverageIcon ||
                !coverageTitle ||
                !coverageSummary ||
                !coverageGaps ||
                !coverageGapList
            ) {
                return;
            }


            const isComplete =
                coverage.isComplete === true;


            /*
            * ICON
            */
            coverageIcon.classList.remove(
                'fa-circle-check',
                'fa-triangle-exclamation',
                'text-success',
                'text-warning'
            );

            if (isComplete) {

                coverageIcon.classList.add(
                    'fa-circle-check',
                    'text-success'
                );

            } else {

                coverageIcon.classList.add(
                    'fa-triangle-exclamation',
                    'text-warning'
                );
            }


            /*
            * TEXT
            */
            setText(
                coverageTitle,
                isComplete
                    ? 'Full week covered'
                    : 'Rate coverage incomplete'
            );

            setText(
                coverageSummary,
                isComplete
                    ? 'Every day and time has an active rate period.'
                    : 'Some journey times do not currently have an active rate period.'
            );


            /*
            * CLEAR OLD GAP ROWS
            */
            coverageGapList.replaceChildren();


            /*
            * COMPLETE WEEK
            */
            if (isComplete) {

                coverageGaps.classList.add(
                    'd-none'
                );

                return;
            }


            /*
            * INCOMPLETE WEEK
            */
            coverageGaps.classList.remove(
                'd-none'
            );


            const gaps =
                Array.isArray(
                    coverage.gaps
                )
                    ? coverage.gaps
                    : [];


            gaps.forEach(
                function (gap) {

                    const row =
                        document.createElement(
                            'div'
                        );

                    row.className =
                        'd-flex ' +
                        'justify-content-between ' +
                        'align-items-center ' +
                        'flex-wrap gap-2';


                    const day =
                        document.createElement(
                            'span'
                        );

                    day.className =
                        'text-muted';

                    setText(
                        day,
                        gap.dayLabel || ''
                    );


                    const time =
                        document.createElement(
                            'strong'
                        );

                    setText(
                        time,
                        (
                            gap.startTime || ''
                        ) +
                        ' → ' +
                        (
                            gap.endTime || ''
                        )
                    );


                    row.append(
                        day,
                        time
                    );

                    coverageGapList.appendChild(
                        row
                    );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | DISPLAY HELPERS
        |--------------------------------------------------------------------------
        */

        function displayTime(
            period
        ) {

            return (
                period.startTime +
                ' → ' +
                period.endTime +
                (
                    period.isOvernight
                        ? ' · overnight'
                        : ''
                )
            );
        }


        function displayDays(
            period
        ) {

            if (
                !Array.isArray(
                    period.dayLabels
                )
            ) {
                return '';
            }

            return period.dayLabels.join(
                ' · '
            );
        }


        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */

        function updateStatusButton(
            row,
            period
        ) {

            if (!row) {
                return;
            }

            const button =
                row.querySelector(
                    '.js-rate-period-toggle'
                );

            if (!button) {
                return;
            }

            button.dataset.active =
                period.isActive
                    ? '1'
                    : '0';

            button.classList.toggle(
                'is-active',
                period.isActive
            );

            button.classList.toggle(
                'is-inactive',
                !period.isActive
            );

            const label =
                button.querySelector(
                    '.js-rate-period-status-label'
                );

            setText(
                label,
                period.isActive
                    ? 'Active'
                    : 'Inactive'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | CREATE ROW
        |--------------------------------------------------------------------------
        */

        function createPeriodRow(
            period
        ) {

            const row =
                document.createElement(
                    'div'
                );

            row.className =
                'admin-zone-row';

            row.dataset.ratePeriodRow =
                String(
                    period.pricingPeriodID
                );


            const main =
                document.createElement(
                    'div'
                );

            main.className =
                'admin-zone-row__main';


            const icon =
                document.createElement(
                    'div'
                );

            icon.className =
                'admin-zone-row__icon';


            const iconElement =
                document.createElement(
                    'i'
                );

            iconElement.className =
                'fa-solid fa-clock';

            icon.appendChild(
                iconElement
            );


            const copy =
                document.createElement(
                    'div'
                );


            const title =
                document.createElement(
                    'div'
                );

            title.className =
                'admin-zone-row__title';

            title.dataset.ratePeriodName =
                '';

            setText(
                title,
                period.periodName
            );


            const time =
                document.createElement(
                    'div'
                );

            time.className =
                'admin-zone-row__description';

            time.dataset.ratePeriodTime =
                '';

            setText(
                time,
                displayTime(
                    period
                )
            );


            const meta =
                document.createElement(
                    'div'
                );

            meta.className =
                'admin-zone-row__meta';


            const days =
                document.createElement(
                    'span'
                );

            days.dataset.ratePeriodDays =
                '';

            setText(
                days,
                displayDays(
                    period
                )
            );


            meta.appendChild(
                days
            );


            copy.append(
                title,
                time,
                meta
            );


            main.append(
                icon,
                copy
            );


            const actions =
                document.createElement(
                    'div'
                );

            actions.className =
                'admin-zone-row__actions';


            const statusButton =
                document.createElement(
                    'button'
                );

            statusButton.type =
                'button';

            statusButton.className =
                'admin-status-button js-rate-period-toggle';

            statusButton.dataset.ratePeriodId =
                String(
                    period.pricingPeriodID
                );

            statusButton.setAttribute(
                'aria-label',
                'Change rate period status'
            );


            const dot =
                document.createElement(
                    'span'
                );

            dot.className =
                'admin-status-dot';


            const statusLabel =
                document.createElement(
                    'span'
                );

            statusLabel.className =
                'js-rate-period-status-label';


            statusButton.append(
                dot,
                statusLabel
            );


            const editButton =
                document.createElement(
                    'button'
                );

            editButton.type =
                'button';

            editButton.className =
                'admin-icon-button js-rate-period-edit';

            editButton.dataset.ratePeriodId =
                String(
                    period.pricingPeriodID
                );

            editButton.setAttribute(
                'aria-label',
                'Edit rate period'
            );


            const editIcon =
                document.createElement(
                    'i'
                );

            editIcon.className =
                'fa-solid fa-pen';


            editButton.appendChild(
                editIcon
            );


            actions.append(
                statusButton,
                editButton,
            );


            row.append(
                main,
                actions
            );


            updateStatusButton(
                row,
                period
            );


            return row;
        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE / INSERT ROW
        |--------------------------------------------------------------------------
        */

        function upsertPeriodRow(
            period
        ) {

            let row =
                document.querySelector(
                    '[data-rate-period-row="' +
                    period.pricingPeriodID +
                    '"]'
                );


            if (!row) {

                row =
                    createPeriodRow(
                        period
                    );

                periodList.appendChild(
                    row
                );

                if (emptyState) {
                    emptyState.classList.add(
                        'd-none'
                    );
                }

            } else {

                setText(
                    row.querySelector(
                        '[data-rate-period-name]'
                    ),
                    period.periodName
                );

                setText(
                    row.querySelector(
                        '[data-rate-period-time]'
                    ),
                    displayTime(
                        period
                    )
                );

                setText(
                    row.querySelector(
                        '[data-rate-period-days]'
                    ),
                    displayDays(
                        period
                    )
                );

                updateStatusButton(
                    row,
                    period
                );
            }


            return row;
        }


        /*
        |--------------------------------------------------------------------------
        | DRAWER
        |--------------------------------------------------------------------------
        */

        function selectedDays()
        {
            return dayFields
                .filter(
                    function (field) {
                        return field.checked;
                    }
                )
                .map(
                    function (field) {
                        return field.value;
                    }
                );
        }


        function setSelectedDays(
            days
        ) {

            const selected =
                new Set(
                    (days || []).map(
                        function (day) {
                            return String(day);
                        }
                    )
                );

            dayFields.forEach(
                function (field) {

                    field.checked =
                        selected.has(
                            field.value
                        );
                }
            );
        }


        function populateDrawer(
            period
        ) {

            periodIDField.value =
                String(
                    period.pricingPeriodID
                );

            periodNameField.value =
                period.periodName || '';

            startTimeField.value =
                period.startTime || '';

            endTimeField.value =
                period.endTime || '';


            activeField.checked =
                period.isActive === true;

            setSelectedDays(
                period.days
            );

            drawerTitle.textContent =
                'Edit Rate Period';

            hideDrawerMessage();

            daysError.classList.add(
                'd-none'
            );
        }


        function prepareNewPeriod()
        {

            periodForm.reset();

            periodIDField.value =
                '';

            activeField.checked =
                true;

            setSelectedDays(
                []
            );

            drawerTitle.textContent =
                'Add Rate Period';

            hideDrawerMessage();

            daysError.classList.add(
                'd-none'
            );

            startTimeField.classList.remove(
                'is-invalid'
            );

            endTimeField.classList.remove(
                'is-invalid'
            );

            drawer.show();

            window.setTimeout(
                function () {
                    periodNameField.focus();
                },
                250
            );
        }


        /*
        |--------------------------------------------------------------------------
        | ADD
        |--------------------------------------------------------------------------
        */

        addButton.addEventListener(
            'click',
            prepareNewPeriod
        );


        /*
        |--------------------------------------------------------------------------
        | EDIT / TOGGLE
        |--------------------------------------------------------------------------
        */

        periodList.addEventListener(
            'click',
            async function (event) {

                const editButton =
                    event.target.closest(
                        '.js-rate-period-edit'
                    );

                const statusButton =
                    event.target.closest(
                        '.js-rate-period-toggle'
                    );

                if (editButton) {

                    const periodID =
                        parseInt(
                            editButton.dataset.ratePeriodId,
                            10
                        );

                    if (!periodID) {
                        return;
                    }

                    editButton.disabled =
                        true;

                    try {

                        const result =
                            await ratePeriodRequest(
                                {
                                    action:
                                        'get_period',

                                    pricingPeriodID:
                                        String(
                                            periodID
                                        )
                                }
                            );

                        populateDrawer(
                            result.period
                        );

                        drawer.show();

                    } catch (error) {

                        console.error(
                            error
                        );

                        showToast(
                            error.message,
                            'error'
                        );

                    } finally {

                        editButton.disabled =
                            false;
                    }

                    return;
                }


                if (statusButton) {

                    const periodID =
                        parseInt(
                            statusButton.dataset.ratePeriodId,
                            10
                        );

                    if (!periodID) {
                        return;
                    }

                    const currentlyActive =
                        statusButton.dataset.active ===
                        '1';

                    const nextActive =
                        !currentlyActive;

                    statusButton.disabled =
                        true;

                    try {

                        const result =
                            await ratePeriodRequest(
                                {
                                    action:
                                        'toggle_period',

                                    pricingPeriodID:
                                        String(
                                            periodID
                                        ),

                                    isActive:
                                        nextActive
                                            ? '1'
                                            : '0'
                                }
                            );

                        upsertPeriodRow(
                            result.period
                        );

                        updateCoverage(
                            result.coverage
                        );

                        if (
                            parseInt(
                                periodIDField.value,
                                10
                            ) ===
                            periodID
                        ) {
                            activeField.checked =
                                result.period.isActive;
                        }

                        showToast(
                            result.message
                        );

                    } catch (error) {

                        console.error(
                            error
                        );

                        showToast(
                            error.message,
                            'error'
                        );

                    } finally {

                        statusButton.disabled =
                            false;
                    }
                }
            }
        );


        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        function validateForm()
        {
            let valid =
                true;


            if (
                !periodNameField.value.trim()
            ) {

                periodNameField.classList.add(
                    'is-invalid'
                );

                valid =
                    false;

            } else {

                periodNameField.classList.remove(
                    'is-invalid'
                );
            }


            if (!startTimeField.value) {

                startTimeField.classList.add(
                    'is-invalid'
                );

                valid =
                    false;

            } else {

                startTimeField.classList.remove(
                    'is-invalid'
                );
            }


            if (
                !endTimeField.value ||
                endTimeField.value ===
                startTimeField.value
            ) {

                endTimeField.classList.add(
                    'is-invalid'
                );

                valid =
                    false;

            } else {

                endTimeField.classList.remove(
                    'is-invalid'
                );
            }


            const days =
                selectedDays();

            daysError.classList.toggle(
                'd-none',
                days.length > 0
            );

            if (days.length === 0) {
                valid =
                    false;
            }


            return valid;
        }


        /*
        |--------------------------------------------------------------------------
        | SAVE
        |--------------------------------------------------------------------------
        */

        periodForm.addEventListener(
            'submit',
            async function (event) {

                event.preventDefault();

                hideDrawerMessage();


                if (!validateForm()) {

                    showDrawerMessage(
                        'Please check the highlighted fields.',
                        'error'
                    );

                    return;
                }


                saveButton.disabled =
                    true;

                saveButton.textContent =
                    'Saving...';


                try {

                    const result =
                        await ratePeriodRequest(
                            {
                                action:
                                    'save_period',

                                pricingPeriodID:
                                    periodIDField.value,

                                periodName:
                                    periodNameField
                                        .value
                                        .trim(),

                                startTime:
                                    startTimeField.value,

                                endTime:
                                    endTimeField.value,

                                days:
                                    selectedDays(),

                                isActive:
                                    activeField.checked
                                        ? '1'
                                        : '0'
                            }
                        );


                    upsertPeriodRow(
                        result.period
                    );


                    updateCoverage(
                        result.coverage
                    );


                    showToast(
                        result.message
                    );


                    /*
                    * Rate Period changes are easiest to review
                    * from the main list, so close the drawer
                    * after a successful save.
                    */
                    drawer.hide();


                } catch (error) {

                    console.error(
                        error
                    );

                    showDrawerMessage(
                        error.message,
                        'error'
                    );


                    if (ajaxMessage) {

                        ajaxMessage.setAttribute(
                            'tabindex',
                            '-1'
                        );

                        ajaxMessage.scrollIntoView(
                            {
                                behavior: 'smooth',
                                block: 'start'
                            }
                        );

                        window.setTimeout(
                            function () {

                                ajaxMessage.focus(
                                    {
                                        preventScroll: true
                                    }
                                );

                            },
                            300
                        );
                    }


                } finally {

                    saveButton.disabled =
                        false;

                    saveButton.textContent =
                        'Save Rate Period';
                }
            }
        );


        /*
        |--------------------------------------------------------------------------
        | CLEAR VALIDATION
        |--------------------------------------------------------------------------
        */

        periodNameField.addEventListener(
            'input',
            function () {

                if (
                    periodNameField
                        .value
                        .trim()
                ) {
                    periodNameField.classList.remove(
                        'is-invalid'
                    );
                }
            }
        );


        startTimeField.addEventListener(
            'input',
            function () {

                if (startTimeField.value) {
                    startTimeField.classList.remove(
                        'is-invalid'
                    );
                }
            }
        );


        endTimeField.addEventListener(
            'input',
            function () {

                if (
                    endTimeField.value &&
                    endTimeField.value !==
                    startTimeField.value
                ) {
                    endTimeField.classList.remove(
                        'is-invalid'
                    );
                }
            }
        );


        dayFields.forEach(
            function (field) {

                field.addEventListener(
                    'change',
                    function () {

                        if (
                            selectedDays().length > 0
                        ) {
                            daysError.classList.add(
                                'd-none'
                            );
                        }
                    }
                );
            }
        );


        /*
        |--------------------------------------------------------------------------
        | DRAWER RESET
        |--------------------------------------------------------------------------
        */

        drawerElement.addEventListener(
            'hidden.bs.offcanvas',
            function () {

                hideDrawerMessage();

                periodNameField.classList.remove(
                    'is-invalid'
                );

                startTimeField.classList.remove(
                    'is-invalid'
                );

                endTimeField.classList.remove(
                    'is-invalid'
                );

                daysError.classList.add(
                    'd-none'
                );
            }
        );

    }
);