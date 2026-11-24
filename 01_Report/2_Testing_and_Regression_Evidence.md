# Week 15 Testing and Regression Evidence

Project 23 Enrollment Requirement Validator. Sam Russell M. So, BSIT-3A. IA6, 18 November 2026.

## Recorded results

| Layer | Observed final result | Evidence |
|---|---|---|
| Laravel | 23 tests and 80 assertions passed | backend-after.txt and backend-after.xml |
| Flutter | 19 tests passed | flutter-after.txt |
| Static analysis | No issues found | flutter-analyze.txt |
| Debug web build | Successful | flutter-build.txt |
| Postman Newman | 43 verified HTTPS requests, 130 assertions, zero failures | postman-newman-results.json and JUnit |
| Dependency audit | No reported advisories after compatible updates | composer-audit-after.json |
| Browser | See final recorded checks, captures and page errors | browser-check.json |

## Controlled failures and improvements

Backend baseline reproduced one failing test of 16 new tests. The first client run reproduced four failing tests of 10. Further screen-state tests reproduced overflow and an abandoned future during expiry. The final suite contains more tests than the baseline because timeout, invalid pagination and screen-state coverage were added. Before/after logs remain separate.

| ID | Severity | Finding | Observed failure | Fix | Regression | Status |
|---|---|---|---|---|---|---|
| F01 | High | Duplicate requirement assignments | Backend baseline W15_01 returned 201 instead of 422. | Scoped validation, composite unique index, safe race conflict response. | Week15HardeningTest::test_w15_01 | Fixed |
| F02 | High | Non-JSON 401 left the browser authenticated | D01 expected one expiry callback; actual zero. | Handle HTTP 401 and remove token before parsing the body. | D01; W06; B04 | Fixed |
| F03 | Medium | Server diagnostics displayed to the user | D02 exposed the fictional private database marker. | Use a generic message for 5xx responses. | D02; D10 | Fixed |
| F04 | Medium | Successful JSON array accepted as an object response | D03 expected an error but received an empty object. | Require an object-shaped JSON response. | D03; D05 | Fixed |
| F05 | Medium | Staff review queue omitted later pages | D04 expected two pages; actual first page only. | Follow bounded API pagination up to 100 pages; report invalid metadata. | D04; D12; W15_08 | Fixed |
| F06 | Medium | Login validation overflowed the available height | W01 and W06 produced RenderFlex overflow in the first screen test run. | Scrollable login viewport with a minimum viewport height. | W01; W06; B05 | Fixed |
| F07 | Medium | Session expiry abandoned a refresh future | W06 exposed an unhandled error when authentication removed the screen. | Attach a handler to refresh futures before handing them to FutureBuilder. | W06; B04 | Fixed |
| F08 | Dependency | Inherited packages had security advisories | Composer locked-package audit returned framework and Symfony advisories. | Update 23 compatible packages; Laravel 12.73.0, Symfony 7.4.23; preserve lockfile. | composer-audit-before.json; composer-audit-after.json | Fixed at audit time |

## Verification interpretation

Laravel tests use isolated in-memory SQLite. Flutter deterministic tests use controlled HTTP doubles; actual browser and Newman runs cover the integration boundary separately. No load, UAT or production penetration result is claimed. TLS verification was enabled with an explicitly trusted local test certificate. The raw TLS record identifies the certificate verification result, HTTPS response, HSTS and plain-HTTP login denial. No private key is distributed.

The evidence gallery groups screenshots in the style of the reference archive. Every image is newly captured or explicitly identified as a rendering of real saved test data. The primary source of a test result is its machine-readable or text log, not a caption alone.
