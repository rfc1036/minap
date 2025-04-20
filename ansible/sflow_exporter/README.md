# sflow_exporter integration for IXP Manager

This directory contains playbooks to install the collector and the
Javascript graphs frontend.

The `sflow_exporter-configurator` program is run hourly by a
[systemd timer unit](https://manpages.debian.org/unstable/systemd/systemd.timer.5)
to update the list of MAC addresses in the configuration file of
[sflow_exporter](https://github.com/dd-ix/sflow_exporter) using
data exported from [IXP Manager](https://www.ixpmanager.org/) by the
custom template `layer2addresses.foil.php`.

## Installation of the collector

```sh
git clone https://github.com/dd-ix/sflow_exporter
cd sflow_exporter/
cargo build --release
cd ..
ansible-playbook install_collector.yaml
```

## Example sflow configuration for Arista switches

```
sflow sample 1000
sflow polling-interval 10
sflow destination 10.9.9.254
sflow source-interface Loopback0
sflow interface disable default
sflow run

interface Ethernet1/1
   description IXP member
   sflow enable
```

## Example configuration for Prometheus

```sh
# /etc/default/prometheus
ARGS="--storage.tsdb.retention.time=365d"
```

```yaml
# /etc/prometheus/prometheus.yml
scrape_configs:
  - job_name: sflow
    scrape_interval: 60s
    static_configs:
      - targets: ['localhost:9144']
```

