-- New changes: service_details km and date for AMC (amc_masters.id = 685)
-- Set 1st service date to 2026-02-23, then recalc 2nd, 3rd, 4th from that date/km.
-- Same logic: service 2 = service_1_date + 120 days, service_1_km + 4000; then +119 days and +4000 km each.

-- 1) Get current values
SELECT id, service_no, service_date, service_km
FROM service_details
WHERE amc_id = 685
ORDER BY service_no ASC;

-- 2) Compute from 1st service:
--    1st service: service_date = 2026-02-23, service_km = 32344
--    service_no 2: date = 2026-02-23 + 120 days = 2026-06-23, km = 32344 + 4000 = 36344
--    service_no 3: date = 2026-06-23 + 119 days = 2026-10-20, km = 36344 + 4000 = 40344
--    service_no 4: date = 2026-10-20 + 119 days = 2027-02-16, km = 40344 + 4000 = 44344

-- 3) Update all services
UPDATE service_details SET service_date = '2026-02-23 00:00:00', service_km = 32344 WHERE amc_id = 685 AND service_no = 1;
UPDATE service_details SET service_date = '2026-06-23 00:00:00', service_km = 36344 WHERE amc_id = 685 AND service_no = 2;
UPDATE service_details SET service_date = '2026-10-20 00:00:00', service_km = 40344 WHERE amc_id = 685 AND service_no = 3;
UPDATE service_details SET service_date = '2027-02-16 00:00:00', service_km = 44344 WHERE amc_id = 685 AND service_no = 4;
