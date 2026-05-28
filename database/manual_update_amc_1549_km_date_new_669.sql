-- New changes: service_details km and date for AMC (amc_masters.id = 669)
-- Set 1st service date to 2026-02-20, then recalc 2nd, 3rd, 4th from that date/km.
-- Same logic: service 2 = service_1_date + 120 days, service_1_km + 4000; then +119 days and +4000 km each.

-- 1) Get current values
SELECT id, service_no, service_date, service_km
FROM service_details
WHERE amc_id = 669
ORDER BY service_no ASC;

-- 2) Compute from 1st service:
--    1st service: service_date = 2026-02-20, service_km = 12598
--    service_no 2: date = 2026-02-20 + 120 days = 2026-06-20, km = 12598 + 4000 = 16598
--    service_no 3: date = 2026-06-20 + 119 days = 2026-10-17, km = 16598 + 4000 = 20598
--    service_no 4: date = 2026-10-17 + 119 days = 2027-02-13, km = 20598 + 4000 = 24598

-- 3) Update all services
UPDATE service_details SET service_date = '2026-02-20 00:00:00', service_km = 12598 WHERE amc_id = 669 AND service_no = 1;
UPDATE service_details SET service_date = '2026-06-20 00:00:00', service_km = 16598 WHERE amc_id = 669 AND service_no = 2;
UPDATE service_details SET service_date = '2026-10-17 00:00:00', service_km = 20598 WHERE amc_id = 669 AND service_no = 3;
UPDATE service_details SET service_date = '2027-02-13 00:00:00', service_km = 24598 WHERE amc_id = 669 AND service_no = 4;
