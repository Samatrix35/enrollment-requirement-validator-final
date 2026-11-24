# Metric definitions

- Application requests: authenticated checklist reads, submitted-queue reads, submissions and staff returns. Each status is compared with the expected 200. The deliberately failed deployment and expected negative Postman checks are outside this count.
- Latency: wall-clock time from sending one request until its full body completes. Percentiles use nearest rank after ascending sort, at index ceil(q*N)-1. Small sample counts make p99 close to or equal to the maximum.
- Throughput: completed application requests divided by actual elapsed run time, including in-flight work and the final probe drain. Warmup and health probes are excluded from the numerator.
- Health latency: separately recorded complete-response readiness probe time. HTTP 200 indicates the dependency/schema check passed; it does not mean the 1000 ms latency goal passed.
- CPU percent: delta Windows process TotalProcessorTime seconds / delta sample elapsed seconds * 100. It is a one-core equivalent, not total computer utilization. CPU sample windows can omit fractions at run edges.
- Sampled peak RSS: maximum observed Windows WorkingSet64 divided by 1,048,576. It includes shared pages and is not an absolute high-water mark or PHP heap measurement. Private bytes are retained separately. TLS proxy samples are available in raw data; backend figures do not include total-stack memory.
- Rollback recovery time: rollback-start timestamp to restored-readiness timestamp. Detection time is reported separately. Authenticated and business-record checks follow restoration.
- Checkpoint preservation: the stopped database copy hash matched immediately after restoration; one selected business record also matched after an authenticated request. This does not prove an ongoing production RPO of zero.
