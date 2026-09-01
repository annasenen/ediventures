document.addEventListener(
    'DOMContentLoaded',
    function () {

        'use strict';


        /*
        |--------------------------------------------------------------------------
        | ELEMENTS
        |--------------------------------------------------------------------------
        */

        const addAirportButton =
            document.getElementById(
                'addAirportButton'
            );

        const searchField =
            document.getElementById(
                'airportSearch'
            );

        const statusFilter =
            document.getElementById(
                'airportStatusFilter'
            );

        const tableBody =
            document.getElementById(
                'airportTableBody'
            );

        const mobileList =
            document.getElementById(
                'airportMobileList'
            );

        const configuredCount =
            document.getElementById(
                'airportConfiguredCount'
            );

        const configuredLabel =
            document.getElementById(
                'airportConfiguredLabel'
            );

        const drawerElement =
            document.getElementById(
                'airportDrawer'
            );

        const drawerTitle =
            document.getElementById(
                'airportDrawerTitle'
            );

        const drawerForm =
            document.getElementById(
                'airportDrawerForm'
            );

        const drawerAction =
            document.getElementById(
                'drawerAction'
            );

        const drawerAirportID =
            document.getElementById(
                'drawerAirportID'
            );

        const referenceSelect =
            document.getElementById(
                'drawerAirportReference'
            );

        const activeField =
            document.getElementById(
                'drawerAirportActive'
            );

        const addFields =
            document.getElementById(
                'airportAddFields'
            );

        const editFields =
            document.getElementById(
                'airportEditFields'
            );

        const drawerAirportName =
            document.getElementById(
                'drawerAirportName'
            );

        const drawerAirportCode =
            document.getElementById(
                'drawerAirportCode'
            );

        const saveButton =
            document.getElementById(
                'drawerSaveButton'
            );

        const ajaxMessage =
            document.getElementById(
                'airportAjaxMessage'
            );

        const toastElement =
            document.getElementById(
                'airportToast'
            );

        const toastMessage =
            document.getElementById(
                'airportToastMessage'
            );


        if (
            !drawerElement ||
            !drawerForm
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
        | HELPERS
        |--------------------------------------------------------------------------
        */

        function getCsrfToken()
        {
            const tokenField =
                drawerForm.querySelector(
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

        async function airportRequest(
            values
        ) {

            const formData =
                new FormData();


            /*
             * CSRF token is sent with every
             * state-changing request.
             */
            formData.append(
                'csrf_token',
                getCsrfToken()
            );


            Object.entries(
                values
            ).forEach(
                function ([key, value]) {

                    formData.append(
                        key,
                        value
                    );
                }
            );


            const response =
                await fetch(
                    '/admin/ajax/airports.php',
                    {
                        method: 'POST',

                        body:
                            formData,

                        headers: {
                            'X-Requested-With':
                                'XMLHttpRequest'
                        },

                        /*
                         * Keep the authenticated
                         * admin session cookie attached.
                         */
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
                    'The airport change could not be saved.'
                );
            }


            return result;
        }


        /*
        |--------------------------------------------------------------------------
        | DRAWER MESSAGES
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


        /*
        |--------------------------------------------------------------------------
        | TOAST
        |--------------------------------------------------------------------------
        */

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
        | CONFIGURED AIRPORT COUNT
        |--------------------------------------------------------------------------
        */

        function setConfiguredCount()
        {
            if (
                !configuredCount ||
                !configuredLabel
            ) {
                return;
            }


            const total =
                document.querySelectorAll(
                    '#airportTableBody [data-airport-id]'
                ).length;


            configuredCount.textContent =
                String(total);


            configuredLabel.textContent =
                total === 1
                    ? 'airport'
                    : 'airports';
        }


        /*
        |--------------------------------------------------------------------------
        | SEARCH / FILTER
        |--------------------------------------------------------------------------
        */

        function applyFilters()
        {
            const search =
                (
                    searchField
                        ? searchField.value
                        : ''
                )
                    .toLowerCase()
                    .trim();


            const status =
                statusFilter
                    ? statusFilter.value
                    : 'all';


            document.querySelectorAll(
                '.airport-item'
            ).forEach(
                function (item) {

                    const name =
                        item.dataset.name || '';

                    const code =
                        item.dataset.code || '';

                    const itemStatus =
                        item.dataset.status || '';


                    const matchesSearch =
                        name.includes(search) ||
                        code.includes(search);


                    const matchesStatus =
                        status === 'all' ||
                        status === itemStatus;


                    item.style.display =
                        matchesSearch &&
                        matchesStatus
                            ? ''
                            : 'none';
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE AIRPORT ON SCREEN
        |--------------------------------------------------------------------------
        */

        function updateAirportElements(
            airport
        ) {

            const status =
                airport.isActive
                    ? 'active'
                    : 'inactive';


            /*
             * Update both desktop and
             * mobile versions.
             */
            document.querySelectorAll(
                '[data-airport-id="' +
                airport.airportID +
                '"]'
            ).forEach(
                function (element) {

                    /*
                     * Main desktop row /
                     * mobile card.
                     */
                    if (
                        element.classList.contains(
                            'airport-item'
                        )
                    ) {

                        element.dataset.name =
                            airport.airportName
                                .toLowerCase();


                        element.dataset.code =
                            airport.airportCode
                                .toLowerCase();


                        element.dataset.status =
                            status;
                    }


                    /*
                     * Edit buttons.
                     */
                    if (
                        element.classList.contains(
                            'js-airport-edit'
                        )
                    ) {

                        element.dataset.airportName =
                            airport.airportName;


                        element.dataset.airportCode =
                            airport.airportCode;


                        element.dataset.active =
                            airport.isActive
                                ? '1'
                                : '0';
                    }


                    /*
                     * Active / inactive buttons.
                     */
                    if (
                        element.classList.contains(
                            'js-airport-toggle'
                        )
                    ) {

                        element.dataset.active =
                            airport.isActive
                                ? '1'
                                : '0';


                        element.classList.toggle(
                            'active',
                            airport.isActive
                        );


                        element.classList.toggle(
                            'inactive',
                            !airport.isActive
                        );


                        const label =
                            element.querySelector(
                                '.js-airport-status-label'
                            );


                        if (label) {

                            label.textContent =
                                airport.isActive
                                    ? 'Active'
                                    : 'Inactive';
                        }
                    }
                }
            );


            applyFilters();
        }


        /*
        |--------------------------------------------------------------------------
        | EDIT BUTTON DATA
        |--------------------------------------------------------------------------
        */

        function setEditData(
            button,
            airport
        ) {

            button.dataset.airportId =
                String(
                    airport.airportID
                );


            button.dataset.airportName =
                airport.airportName;


            button.dataset.airportCode =
                airport.airportCode;


            button.dataset.active =
                airport.isActive
                    ? '1'
                    : '0';
        }


        /*
        |--------------------------------------------------------------------------
        | CREATE EDIT ICON BUTTON
        |--------------------------------------------------------------------------
        */

        function createEditIconButton(
            airport
        ) {

            const button =
                document.createElement(
                    'button'
                );


            button.type =
                'button';


            button.className =
                'admin-icon-button js-airport-edit';


            button.setAttribute(
                'aria-label',
                'Edit airport'
            );


            button.title =
                'Edit';


            setEditData(
                button,
                airport
            );


            const icon =
                document.createElement(
                    'i'
                );


            icon.className =
                'fa-solid fa-pen';


            button.appendChild(
                icon
            );


            return button;
        }


        /*
        |--------------------------------------------------------------------------
        | CREATE STATUS BUTTON
        |--------------------------------------------------------------------------
        */

        function createStatusButton(
            airport
        ) {

            const button =
                document.createElement(
                    'button'
                );


            button.type =
                'button';


            button.className =
                'admin-status-toggle js-airport-toggle ' +
                (
                    airport.isActive
                        ? 'active'
                        : 'inactive'
                );


            button.dataset.airportId =
                String(
                    airport.airportID
                );


            button.dataset.active =
                airport.isActive
                    ? '1'
                    : '0';


            const dot =
                document.createElement(
                    'span'
                );


            dot.className =
                'admin-status-dot';


            const label =
                document.createElement(
                    'span'
                );


            label.className =
                'js-airport-status-label';


            label.textContent =
                airport.isActive
                    ? 'Active'
                    : 'Inactive';


            button.append(
                dot,
                label
            );


            return button;
        }


        /*
        |--------------------------------------------------------------------------
        | CREATE DESKTOP ROW
        |--------------------------------------------------------------------------
        */

        function createDesktopRow(
            airport
        ) {

            const row =
                document.createElement(
                    'tr'
                );


            row.className =
                'airport-item';


            row.dataset.airportId =
                String(
                    airport.airportID
                );


            row.dataset.name =
                airport.airportName
                    .toLowerCase();


            row.dataset.code =
                airport.airportCode
                    .toLowerCase();


            row.dataset.status =
                airport.isActive
                    ? 'active'
                    : 'inactive';


            /*
             * Airport name.
             */

            const nameCell =
                document.createElement(
                    'td'
                );


            const nameButton =
                document.createElement(
                    'button'
                );


            nameButton.type =
                'button';


            nameButton.className =
                'admin-row-title js-airport-edit';


            setEditData(
                nameButton,
                airport
            );


            nameButton.textContent =
                airport.airportName;


            nameCell.appendChild(
                nameButton
            );


            /*
             * IATA code.
             */

            const codeCell =
                document.createElement(
                    'td'
                );


            const code =
                document.createElement(
                    'span'
                );


            code.className =
                'admin-code';


            code.textContent =
                airport.airportCode || '—';


            codeCell.appendChild(
                code
            );


            /*
             * Status.
             */

            const statusCell =
                document.createElement(
                    'td'
                );


            statusCell.appendChild(
                createStatusButton(
                    airport
                )
            );


            /*
             * Edit.
             */

            const actionCell =
                document.createElement(
                    'td'
                );


            actionCell.className =
                'text-end';


            actionCell.appendChild(
                createEditIconButton(
                    airport
                )
            );


            row.append(
                nameCell,
                codeCell,
                statusCell,
                actionCell
            );


            return row;
        }


        /*
        |--------------------------------------------------------------------------
        | CREATE MOBILE CARD
        |--------------------------------------------------------------------------
        */

        function createMobileCard(
            airport
        ) {

            const card =
                document.createElement(
                    'div'
                );


            card.className =
                'admin-mobile-card airport-item';


            card.dataset.airportId =
                String(
                    airport.airportID
                );


            card.dataset.name =
                airport.airportName
                    .toLowerCase();


            card.dataset.code =
                airport.airportCode
                    .toLowerCase();


            card.dataset.status =
                airport.isActive
                    ? 'active'
                    : 'inactive';


            const top =
                document.createElement(
                    'div'
                );


            top.className =
                'd-flex justify-content-between gap-3';


            const info =
                document.createElement(
                    'div'
                );


            const title =
                document.createElement(
                    'h3'
                );


            title.className =
                'admin-mobile-title';


            title.textContent =
                airport.airportName;


            const code =
                document.createElement(
                    'div'
                );


            code.className =
                'admin-code';


            code.textContent =
                airport.airportCode || '—';


            info.append(
                title,
                code
            );


            top.append(
                info,
                createEditIconButton(
                    airport
                )
            );


            const statusWrap =
                document.createElement(
                    'div'
                );


            statusWrap.className =
                'mt-3';


            statusWrap.appendChild(
                createStatusButton(
                    airport
                )
            );


            card.append(
                top,
                statusWrap
            );


            return card;
        }


        /*
        |--------------------------------------------------------------------------
        | INSERT NEW AIRPORT
        |--------------------------------------------------------------------------
        */

        function insertAirport(
            airport
        ) {

            if (tableBody) {

                tableBody.appendChild(
                    createDesktopRow(
                        airport
                    )
                );
            }


            if (mobileList) {

                mobileList.appendChild(
                    createMobileCard(
                        airport
                    )
                );
            }


            setConfiguredCount();

            applyFilters();
        }


        /*
        |--------------------------------------------------------------------------
        | OPEN ADD DRAWER
        |--------------------------------------------------------------------------
        */

        function openAddAirport()
        {

            hideDrawerMessage();


            drawerForm.reset();


            drawerTitle.textContent =
                'Add Airport';


            drawerAction.value =
                'add_airport';


            drawerAirportID.value =
                '';


            referenceSelect.value =
                '';


            referenceSelect.required =
                true;


            activeField.checked =
                true;


            addFields.classList.remove(
                'd-none'
            );


            editFields.classList.add(
                'd-none'
            );


            saveButton.textContent =
                'Add Airport';


            drawer.show();
        }


        /*
        |--------------------------------------------------------------------------
        | OPEN EDIT DRAWER
        |--------------------------------------------------------------------------
        */

        function openEditAirport(
            button
        ) {

            hideDrawerMessage();


            drawerTitle.textContent =
                'Airport Settings';


            drawerAction.value =
                'update_airport';


            drawerAirportID.value =
                button.dataset.airportId || '';


            drawerAirportName.textContent =
                button.dataset.airportName || '';


            drawerAirportCode.textContent =
                button.dataset.airportCode || '';


            activeField.checked =
                button.dataset.active ===
                '1';


            addFields.classList.add(
                'd-none'
            );


            editFields.classList.remove(
                'd-none'
            );


            referenceSelect.required =
                false;


            saveButton.textContent =
                'Save';


            drawer.show();
        }


        /*
        |--------------------------------------------------------------------------
        | ADD AIRPORT BUTTON
        |--------------------------------------------------------------------------
        */

        if (addAirportButton) {

            addAirportButton.addEventListener(
                'click',
                openAddAirport
            );
        }


        /*
        |--------------------------------------------------------------------------
        | SEARCH / FILTER EVENTS
        |--------------------------------------------------------------------------
        */

        if (searchField) {

            searchField.addEventListener(
                'input',
                applyFilters
            );
        }


        if (statusFilter) {

            statusFilter.addEventListener(
                'change',
                applyFilters
            );
        }


        /*
        |--------------------------------------------------------------------------
        | EDIT / STATUS EVENT DELEGATION
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'click',
            async function (event) {

                /*
                 * Edit airport.
                 */

                const editButton =
                    event.target.closest(
                        '.js-airport-edit'
                    );


                if (editButton) {

                    openEditAirport(
                        editButton
                    );

                    return;
                }


                /*
                 * Toggle airport.
                 */

                const statusButton =
                    event.target.closest(
                        '.js-airport-toggle'
                    );


                if (!statusButton) {
                    return;
                }


                const airportID =
                    parseInt(
                        statusButton.dataset.airportId,
                        10
                    );


                if (!airportID) {
                    return;
                }


                const currentState =
                    statusButton.dataset.active ===
                    '1';


                const newState =
                    !currentState;


                /*
                 * Disable both desktop/mobile
                 * copies during request.
                 */

                document.querySelectorAll(
                    '.js-airport-toggle[data-airport-id="' +
                    airportID +
                    '"]'
                ).forEach(
                    function (button) {

                        button.disabled =
                            true;
                    }
                );


                try {

                    const result =
                        await airportRequest(
                            {
                                action:
                                    'toggle_airport',

                                airportID:
                                    String(
                                        airportID
                                    ),

                                isActive:
                                    newState
                                        ? '1'
                                        : '0'
                            }
                        );


                    updateAirportElements(
                        result.airport
                    );


                    /*
                     * If this airport is currently
                     * open in the drawer, synchronise
                     * its Active switch too.
                     */

                    if (
                        parseInt(
                            drawerAirportID.value,
                            10
                        ) ===
                        airportID
                    ) {

                        activeField.checked =
                            result.airport.isActive;
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

                    document.querySelectorAll(
                        '.js-airport-toggle[data-airport-id="' +
                        airportID +
                        '"]'
                    ).forEach(
                        function (button) {

                            button.disabled =
                                false;
                        }
                    );
                }
            }
        );


        /*
        |--------------------------------------------------------------------------
        | SAVE DRAWER
        |--------------------------------------------------------------------------
        */

        drawerForm.addEventListener(
            'submit',
            async function (event) {

                event.preventDefault();


                hideDrawerMessage();


                const action =
                    drawerAction.value;


                /*
                 * Client-side safety check only.
                 *
                 * Server still validates allowed
                 * actions independently.
                 */

                if (
                    action !== 'add_airport' &&
                    action !== 'update_airport'
                ) {

                    showDrawerMessage(
                        'Invalid airport action.',
                        'error'
                    );

                    return;
                }


                /*
                 * Adding requires a trusted
                 * airport reference.
                 */

                if (
                    action === 'add_airport' &&
                    !referenceSelect.value
                ) {

                    referenceSelect.classList.add(
                        'is-invalid'
                    );


                    referenceSelect.focus();


                    showDrawerMessage(
                        'Please choose an airport.',
                        'error'
                    );


                    return;
                }


                referenceSelect.classList.remove(
                    'is-invalid'
                );


                saveButton.disabled =
                    true;


                const originalButtonText =
                    saveButton.textContent;


                saveButton.textContent =
                    action === 'add_airport'
                        ? 'Adding...'
                        : 'Saving...';


                try {

                    const values = {

                        action:
                            action,

                        isActive:
                            activeField.checked
                                ? '1'
                                : '0'
                    };


                    if (
                        action === 'add_airport'
                    ) {

                        values.airportReferenceID =
                            referenceSelect.value;

                    } else {

                        values.airportID =
                            drawerAirportID.value;
                    }


                    const result =
                        await airportRequest(
                            values
                        );


                    /*
                     * ADD.
                     */

                    if (
                        action === 'add_airport'
                    ) {

                        insertAirport(
                            result.airport
                        );


                        /*
                         * Prevent selecting this
                         * configured reference again
                         * in this page session.
                         */

                        const usedOption =
                            referenceSelect.querySelector(
                                'option[value="' +
                                result.airport.airportReferenceID +
                                '"]'
                            );


                        if (usedOption) {

                            usedOption.disabled =
                                true;
                        }


                        /*
                         * Keep drawer open and convert
                         * it into normal edit mode.
                         */

                        drawerAction.value =
                            'update_airport';


                        drawerAirportID.value =
                            String(
                                result.airport.airportID
                            );


                        drawerAirportName.textContent =
                            result.airport.airportName;


                        drawerAirportCode.textContent =
                            result.airport.airportCode;


                        activeField.checked =
                            result.airport.isActive;


                        addFields.classList.add(
                            'd-none'
                        );


                        editFields.classList.remove(
                            'd-none'
                        );


                        referenceSelect.required =
                            false;


                        drawerTitle.textContent =
                            'Airport Settings';


                        saveButton.textContent =
                            'Save';


                    /*
                     * UPDATE.
                     */

                    } else {

                        updateAirportElements(
                            result.airport
                        );


                        activeField.checked =
                            result.airport.isActive;
                    }


                    showDrawerMessage(
                        result.message
                    );


                    showToast(
                        result.message
                    );


                } catch (error) {

                    console.error(
                        error
                    );


                    showDrawerMessage(
                        error.message,
                        'error'
                    );


                } finally {

                    saveButton.disabled =
                        false;


                    if (
                        drawerAction.value ===
                        'update_airport'
                    ) {

                        saveButton.textContent =
                            'Save';

                    } else {

                        saveButton.textContent =
                            originalButtonText;
                    }
                }
            }
        );


        /*
        |--------------------------------------------------------------------------
        | REMOVE INVALID STATE AFTER SELECTION
        |--------------------------------------------------------------------------
        */

        referenceSelect.addEventListener(
            'change',
            function () {

                if (
                    referenceSelect.value
                ) {

                    referenceSelect.classList.remove(
                        'is-invalid'
                    );


                    hideDrawerMessage();
                }
            }
        );


        /*
        |--------------------------------------------------------------------------
        | DRAWER CLEANUP
        |--------------------------------------------------------------------------
        */

        drawerElement.addEventListener(
            'hidden.bs.offcanvas',
            function () {

                hideDrawerMessage();


                referenceSelect.classList.remove(
                    'is-invalid'
                );
            }
        );


        /*
        |--------------------------------------------------------------------------
        | INITIAL FILTER
        |--------------------------------------------------------------------------
        */

        applyFilters();
    }
);