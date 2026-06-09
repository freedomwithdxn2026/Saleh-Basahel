---
name: dxn-compliance-security
description: Use when reviewing or building DXN business materials or software that involve health/wellness claims, income claims, direct-selling opportunity claims, testimonials, privacy, customer/member data, payments, order history, commissions, or distributor records.
---

# DXN Compliance And Security

Use this as a risk checklist for DXN business websites, content, automations, and dashboards. It does not replace local legal advice.

## Compliance Review

First check whether the task touches DXN Middle East distributor activity or digital tools. If yes, read `references/dxn-middle-east-rr-memo-2026-05-15.md` before giving advice or building anything.

Review these areas before publishing:

- Product claims: avoid disease treatment, cure, prevention, or medical advice unless approved copy is provided.
- Income claims: avoid guaranteed income, financial freedom promises, or rank/bonus certainty.
- Testimonials: require context, consent, and disclaimers when they imply health or income outcomes.
- Distributor status: clearly distinguish independent distributor/member content from official company content when relevant.
- Country rules: verify local DXN policies and local direct-selling, advertising, privacy, tax, and consumer-protection rules.
- Third-party marketplace rules: check country-specific DXN policy before supporting third-party resale channels.
- Unofficial tools/channels: do not build or recommend websites, applications, registration tools, promotional systems, training portals, external services, or other digital systems that represent, support, promote, boost, or operate DXN business activity unless the user has prior written DXN management approval.
- Cross-promotion: do not use DXN distributor networks, events, social groups, meetings, seminars, or communication channels to promote non-DXN products, services, businesses, investment schemes, marketing programs, or external opportunities.

## Security Review

Treat these as sensitive:

- customer contact details
- order and delivery history
- member/distributor IDs
- sponsor/upline/team relationships
- commission, bonus, payout, and rank/status data
- payment status and transaction references

Default controls:

- Use authentication for member/admin areas.
- Use role-based access for customer, member, team leader, admin, and support views.
- Log changes to order status, payout status, member status, and commission calculations.
- Avoid exposing sequential internal IDs publicly; use random public identifiers.
- Validate server-side pricing, eligibility, and commission inputs.
- Never commit secrets from `.env` or payment/provider credentials.

## Output Style

When reviewing, lead with the highest-risk issues. Include what to change and why. For code, cite file and line references. For content, quote only the minimum phrase needed and provide safer replacement wording.
