## ProjectsTys.md

### When building features, adding new behavior or fixing a bug in this folder:

1. Build a feature
2. Test that feature
   a) Did the rsync work?
   b) Does the feature work in projects.elasticgun.com
   c) Did Codeception smoke tests pass?
3. Did it work?

- Yes: Go to 1, and build next feature.
- No: Fix it, go to 2

---

#### Notes:
- Always follow the `php_style.md` style guide when writing code.
- Use the TYS method for all new features, bug fixes, and improvements in this folder.
- Testing includes both local sync (rsync) and live deployment verification.
- Each change must add or update automated tests (e.g., Livewire feature tests) and run them before declaring the feature complete.
- Always run Codeception smoke tests (functional + prod) before reporting a change as done.
