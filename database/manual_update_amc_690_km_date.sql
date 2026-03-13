-- Manual update: service_details km and date for amc_id = 690
-- 1st service is left UNCHANGED (2026-02-24, 26950). Only 2nd, 3rd, 4th updated from 1st.
-- Logic: service 2 = service_1_date + 120 days, service_1_km + 4000; then +119 days and +4000 km each.

-- 1) Current state (for reference)
SELECT id, service_no, service_date, service_km, status
FROM service_details
WHERE amc_id = 690
ORDER BY service_no ASC;

-- 2) From 1st service (2026-02-24, 26950) — do NOT update service_no 1:
--    service_no 2: 2026-02-24 + 120 days = 2026-06-24, km = 26950 + 4000 = 30950
--    service_no 3: 2026-06-24 + 119 days = 2026-10-21, km = 30950 + 4000 = 34950
--    service_no 4: 2026-10-21 + 119 days = 2027-02-17, km = 34950 + 4000 = 38950

-- 3) Update ONLY from 2nd service onwards
UPDATE service_details SET service_date = '2026-06-24 00:00:00', service_km = 30950 WHERE amc_id = 690 AND service_no = 2;
UPDATE service_details SET service_date = '2026-10-21 00:00:00', service_km = 34950 WHERE amc_id = 690 AND service_no = 3;
UPDATE service_details SET service_date = '2027-02-17 00:00:00', service_km = 38950 WHERE amc_id = 690 AND service_no = 4;
