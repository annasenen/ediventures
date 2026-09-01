document.addEventListener(
    'DOMContentLoaded',
    function () {

        'use strict';


        /*
        |--------------------------------------------------------------------------
        | ELEMENTS
        |--------------------------------------------------------------------------
        */

        const zoneList =
            document.getElementById(
                'zoneList'
            );

        const emptyState =
            document.getElementById(
                'zoneEmptyState'
            );

        const addZoneButton =
            document.getElementById(
                'addZoneButton'
            );

        const drawerElement =
            document.getElementById(
                'zoneDrawer'
            );

        const drawerTitle =
            document.getElementById(
                'zoneDrawerTitle'
            );

        const zoneForm =
            document.getElementById(
                'zoneForm'
            );

        const zoneIDField =
            document.getElementById(
                'zoneID'
            );

        const zoneNameField =
            document.getElementById(
                'zoneName'
            );

        const zoneDescriptionField =
            document.getElementById(
                'zoneDescription'
            );

        const zoneActiveField =
            document.getElementById(
                'zoneActive'
            );

        const saveZoneButton =
            document.getElementById(
                'saveZoneButton'
            );

        const ajaxMessage =
            document.getElementById(
                'zoneAjaxMessage'
            );

        const postcodeSection =
            document.getElementById(
                'zonePostcodeSection'
            );

        const postcodeList =
            document.getElementById(
                'zonePostcodeList'
            );

        const noPostcodes =
            document.getElementById(
                'zoneNoPostcodes'
            );

        const postcodeForm =
            document.getElementById(
                'zonePostcodeForm'
            );

        const postcodeZoneID =
            document.getElementById(
                'postcodeZoneID'
            );

        const postcodeField =
            document.getElementById(
                'postcodePrefix'
            );

        const addPostcodeButton =
            document.getElementById(
                'addPostcodeButton'
            );

        const toastElement =
            document.getElementById(
                'zoneToast'
            );

        const toastMessage =
            document.getElementById(
                'zoneToastMessage'
            );


        if (
            !drawerElement ||
            !zoneForm
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
                zoneForm.querySelector(
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

        async function zoneRequest(
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

                    formData.append(
                        key,
                        value
                    );
                }
            );


            const response =
                await fetch(
                    '/admin/ajax/zones.php',
                    {
                        method: 'POST',

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
                    'The change could not be saved.'
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
        | POSTCODE COUNT
        |--------------------------------------------------------------------------
        */

        function updatePostcodeCount(
            row,
            count
        ) {

            if (!row) {
                return;
            }


            const countElement =
                row.querySelector(
                    '[data-zone-postcode-count]'
                );

            const labelElement =
                row.querySelector(
                    '[data-zone-postcode-label]'
                );


            setText(
                countElement,
                String(count)
            );


            setText(
                labelElement,
                count === 1
                    ? 'postcode rule'
                    : 'postcode rules'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */

        function updateStatusButton(
            row,
            zone
        ) {

            if (!row) {
                return;
            }


            const button =
                row.querySelector(
                    '.js-zone-toggle'
                );

            if (!button) {
                return;
            }


            button.dataset.active =
                zone.isActive
                    ? '1'
                    : '0';


            button.classList.toggle(
                'is-active',
                zone.isActive
            );


            button.classList.toggle(
                'is-inactive',
                !zone.isActive
            );


            const label =
                button.querySelector(
                    '.js-zone-status-label'
                );


            setText(
                label,
                zone.isActive
                    ? 'Active'
                    : 'Inactive'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | CREATE ZONE ROW
        |--------------------------------------------------------------------------
        */

        function createZoneRow(
            zone
        ) {

            const row =
                document.createElement(
                    'div'
                );

            row.className =
                'admin-zone-row';

            row.dataset.zoneRow =
                String(
                    zone.zoneID
                );


            /*
             * LEFT
             */

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
                'fa-solid fa-map-location-dot';

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

            title.dataset.zoneName =
                '';

            setText(
                title,
                zone.zoneName
            );


            const description =
                document.createElement(
                    'div'
                );

            description.className =
                'admin-zone-row__description';

            description.dataset.zoneDescription =
                '';

            setText(
                description,
                zone.description
            );

            description.hidden =
                !zone.description;


            const meta =
                document.createElement(
                    'div'
                );

            meta.className =
                'admin-zone-row__meta';


            const count =
                document.createElement(
                    'span'
                );

            count.dataset.zonePostcodeCount =
                '';

            setText(
                count,
                String(
                    zone.postcodeCount
                )
            );


            const space =
                document.createTextNode(
                    ' '
                );


            const label =
                document.createElement(
                    'span'
                );

            label.dataset.zonePostcodeLabel =
                '';

            setText(
                label,
                zone.postcodeCount === 1
                    ? 'postcode rule'
                    : 'postcode rules'
            );


            meta.append(
                count,
                space,
                label
            );


            copy.append(
                title,
                description,
                meta
            );


            main.append(
                icon,
                copy
            );


            /*
             * RIGHT
             */

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
                'admin-status-button js-zone-toggle';

            statusButton.dataset.zoneId =
                String(
                    zone.zoneID
                );

            statusButton.dataset.active =
                zone.isActive
                    ? '1'
                    : '0';

            statusButton.setAttribute(
                'aria-label',
                'Change zone status'
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
                'js-zone-status-label';


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
                'admin-icon-button js-zone-edit';

            editButton.dataset.zoneId =
                String(
                    zone.zoneID
                );

            editButton.setAttribute(
                'aria-label',
                'Edit zone'
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
                editButton
            );


            row.append(
                main,
                actions
            );


            updateStatusButton(
                row,
                zone
            );


            return row;
        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE / INSERT ROW
        |--------------------------------------------------------------------------
        */

        function upsertZoneRow(
            zone
        ) {

            let row =
                document.querySelector(
                    '[data-zone-row="' +
                    zone.zoneID +
                    '"]'
                );


            if (!row) {

                row =
                    createZoneRow(
                        zone
                    );


                zoneList.appendChild(
                    row
                );


                if (emptyState) {
                    emptyState.classList.add(
                        'd-none'
                    );
                }

            } else {

                const title =
                    row.querySelector(
                        '[data-zone-name]'
                    );

                const description =
                    row.querySelector(
                        '[data-zone-description]'
                    );


                setText(
                    title,
                    zone.zoneName
                );


                setText(
                    description,
                    zone.description
                );


                if (description) {
                    description.hidden =
                        !zone.description;
                }


                updatePostcodeCount(
                    row,
                    zone.postcodeCount
                );


                updateStatusButton(
                    row,
                    zone
                );
            }


            return row;
        }


        /*
        |--------------------------------------------------------------------------
        | POSTCODE CHIPS
        |--------------------------------------------------------------------------
        */

        function renderPostcodes(
            zone
        ) {

            postcodeList.innerHTML =
                '';


            const postcodes =
                Array.isArray(
                    zone.postcodes
                )
                    ? zone.postcodes
                    : [];


            noPostcodes.classList.toggle(
                'd-none',
                postcodes.length > 0
            );


            postcodes.forEach(
                function (postcode) {

                    const chip =
                        document.createElement(
                            'div'
                        );

                    chip.className =
                        'admin-zone-chip';


                    const text =
                        document.createElement(
                            'span'
                        );

                    setText(
                        text,
                        postcode.displayValue
                    );


                    const removeButton =
                        document.createElement(
                            'button'
                        );

                    removeButton.type =
                        'button';

                    removeButton.className =
                        'admin-zone-chip__remove js-remove-postcode';

                    removeButton.dataset.postcodeId =
                        String(
                            postcode.zonePostcodeID
                        );

                    removeButton.dataset.zoneId =
                        String(
                            zone.zoneID
                        );

                    removeButton.setAttribute(
                        'aria-label',
                        'Remove postcode rule'
                    );


                    const icon =
                        document.createElement(
                            'i'
                        );

                    icon.className =
                        'fa-solid fa-xmark';


                    removeButton.appendChild(
                        icon
                    );


                    chip.append(
                        text,
                        removeButton
                    );


                    postcodeList.appendChild(
                        chip
                    );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | LOAD ZONE INTO DRAWER
        |--------------------------------------------------------------------------
        */

        function populateDrawer(
            zone
        ) {

            zoneIDField.value =
                String(
                    zone.zoneID
                );

            postcodeZoneID.value =
                String(
                    zone.zoneID
                );

            zoneNameField.value =
                zone.zoneName || '';

            zoneDescriptionField.value =
                zone.description || '';

            zoneActiveField.checked =
                zone.isActive === true;


            drawerTitle.textContent =
                'Edit Zone';


            postcodeSection.classList.remove(
                'd-none'
            );


            renderPostcodes(
                zone
            );


            hideDrawerMessage();
        }


        /*
        |--------------------------------------------------------------------------
        | ADD NEW ZONE
        |--------------------------------------------------------------------------
        */

        function prepareNewZone()
        {

            zoneForm.reset();


            zoneIDField.value =
                '';

            postcodeZoneID.value =
                '';


            zoneActiveField.checked =
                true;


            drawerTitle.textContent =
                'Add Zone';


            postcodeSection.classList.add(
                'd-none'
            );


            postcodeList.innerHTML =
                '';


            noPostcodes.classList.add(
                'd-none'
            );


            hideDrawerMessage();


            drawer.show();


            window.setTimeout(
                function () {

                    zoneNameField.focus();

                },
                250
            );
        }


        /*
        |--------------------------------------------------------------------------
        | ADD BUTTON
        |--------------------------------------------------------------------------
        */

        addZoneButton.addEventListener(
            'click',
            prepareNewZone
        );


        /*
        |--------------------------------------------------------------------------
        | EVENT DELEGATION - EDIT / STATUS
        |--------------------------------------------------------------------------
        */

        zoneList.addEventListener(
            'click',
            async function (event) {

                const editButton =
                    event.target.closest(
                        '.js-zone-edit'
                    );

                const statusButton =
                    event.target.closest(
                        '.js-zone-toggle'
                    );


                /*
                 * EDIT
                 */
                if (editButton) {

                    const zoneID =
                        parseInt(
                            editButton.dataset.zoneId,
                            10
                        );


                    if (!zoneID) {
                        return;
                    }


                    editButton.disabled =
                        true;


                    try {

                        const result =
                            await zoneRequest(
                                {
                                    action:
                                        'get_zone',

                                    zoneID:
                                        String(
                                            zoneID
                                        )
                                }
                            );


                        populateDrawer(
                            result.zone
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


                /*
                 * ACTIVE / INACTIVE
                 */
                if (statusButton) {

                    const zoneID =
                        parseInt(
                            statusButton.dataset.zoneId,
                            10
                        );


                    if (!zoneID) {
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
                            await zoneRequest(
                                {
                                    action:
                                        'toggle_zone',

                                    zoneID:
                                        String(
                                            zoneID
                                        ),

                                    isActive:
                                        nextActive
                                            ? '1'
                                            : '0'
                                }
                            );


                        upsertZoneRow(
                            result.zone
                        );


                        /*
                         * If the same zone is open
                         * in the drawer, keep its
                         * switch synchronised.
                         */
                        if (
                            parseInt(
                                zoneIDField.value,
                                10
                            ) ===
                            zoneID
                        ) {

                            zoneActiveField.checked =
                                result.zone.isActive;
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
        | SAVE ZONE
        |--------------------------------------------------------------------------
        */

        zoneForm.addEventListener(
            'submit',
            async function (event) {

                event.preventDefault();


                hideDrawerMessage();


                const zoneName =
                    zoneNameField.value.trim();


                if (!zoneName) {

                    zoneNameField.classList.add(
                        'is-invalid'
                    );

                    zoneNameField.focus();

                    return;
                }


                zoneNameField.classList.remove(
                    'is-invalid'
                );


                saveZoneButton.disabled =
                    true;

                saveZoneButton.textContent =
                    'Saving...';


                try {

                    const result =
                        await zoneRequest(
                            {
                                action:
                                    'save_zone',

                                zoneID:
                                    zoneIDField.value,

                                zoneName:
                                    zoneName,

                                description:
                                    zoneDescriptionField
                                        .value
                                        .trim(),

                                /*
                                 * Checkbox values are
                                 * represented by presence.
                                 */
                                ...(zoneActiveField.checked
                                    ? {
                                        isActive:
                                            '1'
                                    }
                                    : {})
                            }
                        );


                    const zone =
                        result.zone;


                    upsertZoneRow(
                        zone
                    );


                    populateDrawer(
                        zone
                    );


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

                    saveZoneButton.disabled =
                        false;

                    saveZoneButton.textContent =
                        'Save Zone';
                }
            }
        );


        /*
        |--------------------------------------------------------------------------
        | CLEAR INVALID NAME
        |--------------------------------------------------------------------------
        */

        zoneNameField.addEventListener(
            'input',
            function () {

                if (
                    zoneNameField.value.trim()
                ) {

                    zoneNameField.classList.remove(
                        'is-invalid'
                    );
                }
            }
        );


        /*
        |--------------------------------------------------------------------------
        | ADD POSTCODE
        |--------------------------------------------------------------------------
        */

        postcodeForm.addEventListener(
            'submit',
            async function (event) {

                event.preventDefault();


                hideDrawerMessage();


                const zoneID =
                    parseInt(
                        postcodeZoneID.value,
                        10
                    );


                const postcode =
                    postcodeField
                        .value
                        .trim();


                if (
                    !zoneID ||
                    !postcode
                ) {
                    return;
                }


                addPostcodeButton.disabled =
                    true;

                addPostcodeButton.textContent =
                    'Adding...';


                try {

                    const result =
                        await zoneRequest(
                            {
                                action:
                                    'add_postcode',

                                zoneID:
                                    String(
                                        zoneID
                                    ),

                                postcodePrefix:
                                    postcode
                            }
                        );


                    postcodeField.value =
                        '';


                    renderPostcodes(
                        result.zone
                    );


                    upsertZoneRow(
                        result.zone
                    );


                    showDrawerMessage(
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

                    addPostcodeButton.disabled =
                        false;

                    addPostcodeButton.textContent =
                        'Add';
                }
            }
        );


        /*
        |--------------------------------------------------------------------------
        | REMOVE POSTCODE
        |--------------------------------------------------------------------------
        */

        postcodeList.addEventListener(
            'click',
            async function (event) {

                const removeButton =
                    event.target.closest(
                        '.js-remove-postcode'
                    );


                if (!removeButton) {
                    return;
                }


                const zoneID =
                    parseInt(
                        removeButton.dataset.zoneId,
                        10
                    );


                const postcodeID =
                    parseInt(
                        removeButton.dataset.postcodeId,
                        10
                    );


                if (
                    !zoneID ||
                    !postcodeID
                ) {
                    return;
                }


                removeButton.disabled =
                    true;


                try {

                    const result =
                        await zoneRequest(
                            {
                                action:
                                    'remove_postcode',

                                zoneID:
                                    String(
                                        zoneID
                                    ),

                                zonePostcodeID:
                                    String(
                                        postcodeID
                                    )
                            }
                        );


                    renderPostcodes(
                        result.zone
                    );


                    upsertZoneRow(
                        result.zone
                    );


                    showDrawerMessage(
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


                    removeButton.disabled =
                        false;
                }
            }
        );


        /*
        |--------------------------------------------------------------------------
        | DRAWER RESET AFTER CLOSE
        |--------------------------------------------------------------------------
        */

        drawerElement.addEventListener(
            'hidden.bs.offcanvas',
            function () {

                hideDrawerMessage();

                zoneNameField.classList.remove(
                    'is-invalid'
                );

                postcodeField.value =
                    '';
            }
        );

    }
);