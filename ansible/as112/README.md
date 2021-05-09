# Ansible playbooks for the MINAP AS112 instance

The `as112.yaml` playbook requires installing
[mitogen](https://mitogen.readthedocs.io/en/stable/ansible.html#installation)
to access the container.

Create the container and configure the host system:

```
ansible-playbook as112-host.yaml
```

On the host, start the container and permanently enable it on reboots:

```
machinectl start as112
machinectl enable as112
```

Configure the container:

```
ansible-playbook as112.yaml
```

You may use the official Grafana dashboard of bind_exporter:
https://grafana.com/dashboards/12309 .

