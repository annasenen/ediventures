# EdiVentures Backend Plan

## Main future features
- Customer accounts
- Airport transfer booking
- Scotland tour booking
- Quote requests
- Admin calendar
- Admin blocked dates/times
- Deposit payments
- Booking and cancellation rules

## Future database tables

### users
Stores customer accounts.

Fields:
- userID
- firstName
- lastName
- email
- phone
- passwordHash
- createdAt

### airports
Stores airports available in the system.

Fields:
- airportID
- airportName
- isOnlineBookable
- requiresManualQuote

### airport_bookings
Stores airport pickup and drop-off bookings.

Fields:
- bookingID
- userID
- journeyType
- airportID
- postcode
- houseNumber
- street
- townCity
- travelDateTime
- flightNumber
- passengers
- largeCases
- smallBags
- oversizedLuggage
- estimatedPrice
- depositAmount
- status
- createdAt

### quote_requests
Stores manual quote requests.

Fields:
- quoteID
- customerName
- email
- phone
- quoteType
- journeyDetails
- passengers
- luggageDetails
- preferredDate
- status
- createdAt

### availability_blocks
Stores admin blocked dates/times.

Fields:
- blockID
- startDateTime
- endDateTime
- reason
- createdAt

### payments
Stores deposits/payment records.

Fields:
- paymentID
- bookingID
- paymentProvider
- providerPaymentID
- amount
- status
- createdAt

## Important business rules

- Customer can check airport booking details before creating an account.
- Customer creates/logs into account only before confirming booking and paying deposit.
- Airport booking must calculate blocked time depending on:
  - airport
  - journey type
  - postcode zone
  - pickup or arrival time
- Admin can block full days or selected time ranges.
- Oversized luggage should require manual quote.
- Other airports should require manual quote unless later enabled.
- Booking is confirmed only after deposit payment.
- Remaining balance is paid to driver.