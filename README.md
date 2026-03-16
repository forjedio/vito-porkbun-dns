# Vito Porkbun DNS Plugin

A [Vito](https://github.com/vitodeploy/vito) plugin that adds [Porkbun](https://porkbun.com) as a DNS provider, enabling domain and DNS record management directly from Vito.

## Summary

Vito ships with Cloudflare as a built-in DNS provider. This plugin adds Porkbun as an additional option using Vito's plugin system — no core files are modified.

Once enabled, the plugin registers Porkbun in the DNS provider list. You can then connect your Porkbun account, import domains, and create, update, or delete DNS records from the Vito dashboard.

### Key differences from Cloudflare

- **Authentication:** Porkbun uses an API key + secret API key pair, sent in the JSON body of every request (not as a bearer token header).
- **Domain identification:** Porkbun uses domain names as identifiers rather than opaque zone IDs.
- **All requests are POST:** Porkbun's API uses POST for every operation, including reads and deletes.
- **No proxy support:** Unlike Cloudflare, Porkbun does not offer a proxying layer. The `proxied` flag is always `false`.

### Supported operations

- Connect and authenticate with Porkbun API
- List and retrieve domains
- Create, update, and delete DNS records

## Setup

1. Generate an API key at [porkbun.com/account/api](https://porkbun.com/account/api).
2. Make sure API access is enabled for each domain you want to manage (this is a per-domain setting in the Porkbun dashboard).
3. Enable the plugin in Vito.
4. Add a new DNS provider, select **Porkbun**, and enter your API key and secret API key.

## Domain Filter

Porkbun's API returns **all domains** on your account when listing domains, regardless of whether they have API access enabled. This means domains that cannot actually be managed via the API will still appear in the domain selection list. Attempting to add one of these domains will result in an error when Vito tries to sync its DNS records.

To work around this, the plugin provides an optional **Domain Filter** field when configuring your Porkbun connection. You can enter a comma-separated list of domain names (e.g. `example.com, mysite.org`) to restrict which domains appear in the list. Only domains matching the filter will be shown.

If the field is left empty, all domains on your account will be returned.

## Community & Open Source

This plugin is **community-built and open source**. It is not officially maintained by the Vito core team.

Contributions, bug reports, and feature requests are welcome at [github.com/forjedio/vito-porkbun-dns](https://github.com/forjedio/vito-porkbun-dns).

Licensed under the same terms as the Vito project.
