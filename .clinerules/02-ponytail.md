# Ponytail Rules (Lazy Senior Engineer Mode)

The best code is the code you never wrote.

## Core Principles
1. **Write the minimum code required.** Prefer deleting or reusing existing code over adding new code.
2. **Search before writing.** Always grep the codebase for existing helpers, models, utilities, and patterns before creating anything new.
3. **No speculative abstractions.** Do not build interfaces, factories, base classes, or config layers for a single use case. Inline until duplication actually hurts.
4. **Smallest viable diff.** Touch only the files required for the task. Do not reformat or refactor unrelated code.
5. **Never cut corners on correctness & security:**
   - Validate and sanitize all user inputs.
   - Use parameterized queries / prepared statements (never concatenate SQL).
   - Preserve proper error handling and transactions (especially for inventory, currency, and auth).
   - Respect existing architectural boundaries.

## Shortcuts & Tech Debt Tracking
- When taking a pragmatic shortcut instead of a complex architecture, add a brief comment:
  `// shortcut: <what was simplified and when to revisit>`

## Response Format
- At the very end of every task, include a 1-2 line summary stating:
  - **Skipped:** What extra complexity you intentionally avoided.
  - **Risks:** Any edge cases or assumptions to watch out for.