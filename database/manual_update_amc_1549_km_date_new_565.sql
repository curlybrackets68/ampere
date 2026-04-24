-- New changes: service_details km and date for AMC 1549 (amc_masters.id = 565)
-- 1st service is left UNCHANGED. Only 2nd, 3rd, 4th... are updated from 1st service's date/km.
-- Same logic: service 2 = service_1_date + 120 days, service_1_km + 4000; then +119 days and +4000 km each.

-- 1) Get 1st service date and km (use these values in step 2)
SELECT id, service_no, service_date, service_km
FROM service_details
WHERE amc_id = 565
ORDER BY service_no ASC;

-- 2) Compute from 1st service (do NOT update service_no 1):
--    1st service: service_date = 2026-02-21, service_km = 351 (unchanged)
--    service_no 2: date = 2026-02-21 + 120 days = 2026-06-21, km = 351 + 4000 = 4351
--    service_no 3: date = 2026-06-21 + 119 days = 2026-10-18, km = 4351 + 4000 = 8351
--    service_no 4: date = 2026-10-18 + 119 days = 2027-02-14, km = 8351 + 4000 = 12351

-- 3) Update ONLY from 2nd service onwards
UPDATE service_details SET service_date = '2026-06-21 00:00:00', service_km = 4351 WHERE amc_id = 565 AND service_no = 2;
UPDATE service_details SET service_date = '2026-10-18 00:00:00', service_km = 8351 WHERE amc_id = 565 AND service_no = 3;
UPDATE service_details SET service_date = '2027-02-14 00:00:00', service_km = 12351 WHERE amc_id = 565 AND service_no = 4;
