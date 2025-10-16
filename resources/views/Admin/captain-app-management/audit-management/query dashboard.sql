select count(*) as 'Count of total activation forms submitted for whom Listing Mapping is not done' from hostaboard as h where id not  in (select hostaboard_id from auditlistingmapping );
SELECT count(*) as 'Count of Total Photography Tasks Pending' FROM `revenue_activation_audit` where task_status != 'no required' and status != 'mark as done';
SELECT count(*) as 'Count of Total Photography in Review' FROM `revenue_activation_audit` where status = 'mark as done';
SELECT count(*) as 'Count of Activation Audit Tasks pending' FROM `audits`  WHERE status NOT IN ('mark as done', 'completed') OR status IS NULL;
SELECT COUNT(DISTINCT hostaboard_id) AS 'Count of Inventory/Maintenance Requests Pending' FROM `sales_activation_audit` WHERE task_type IN ('Maintenance', 'Inventory') AND status != 'approved' AND minor_major = 1;
SELECT count(*) as 'Count of Deep Cleaning Tasks Pending' FROM `deep_cleanings` where status not in ('mark as done','completed') or status is null;
SELECT count(id) as 'Count of Cohosting Account Set Up Pending' FROM `audit_backend_ops` where status not in ('approved') or status is null;
SELECT count(*) as 'Count of Listing Creation Pending' FROM `auditlisting` where status not in ('Approved','approved') or status is null
SELECT count(*) as 'Count of Listing Mapping Pending' FROM `auditlistingmapping` where status not in ('Approved','approved') or status is null
SELECT count(*) as 'Count of Total Properties whose Mapping has been Completed' FROM `auditlistingmapping` where status  in ('Approved','approved') 