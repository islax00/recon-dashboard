FROM golang:1.26.4 AS builder

RUN go install github.com/projectdiscovery/subfinder/v2/cmd/subfinder@latest
RUN go install github.com/projectdiscovery/httpx/cmd/httpx@latest
RUN go install github.com/projectdiscovery/nuclei/v3/cmd/nuclei@latest
RUN go install github.com/projectdiscovery/naabu/v2/cmd/naabu@latest
RUN go install github.com/projectdiscovery/katana/cmd/katana@latest

FROM ubuntu:22.04

RUN apt-get update && apt-get install -y \
    ca-certificates \
    python3 \
    python3-pip \
    && rm -rf /var/lib/apt/lists/*

RUN pip3 install flask

COPY --from=builder /go/bin/* /usr/local/bin/
COPY api.py /app/api.py

CMD ["python3", "/app/api.py"]