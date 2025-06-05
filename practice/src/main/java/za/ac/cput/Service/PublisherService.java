package za.ac.cput.Service;

import org.springframework.stereotype.Service;
import za.ac.cput.Domain.Publisher;
import za.ac.cput.Repository.PublisherRepository;

import java.util.List;

@Service
public class PublisherService {
    private final PublisherRepository publisherRepository;

    public PublisherService(PublisherRepository publisherRepository) {
        this.publisherRepository = publisherRepository;
    }

    public Publisher save(Publisher publisher) {
        return publisherRepository.save(publisher);
    }
    public Publisher readPublisherById(Long publisherId) {
        return publisherRepository.findById(publisherId).orElse(null);
    }
    public Publisher updatePublisher(Publisher publisher) {
        if (publisherRepository.existsById(publisher.getPublisherId())) {
            return publisherRepository.save(publisher);
        }
        return null; // or throw an exception if preferred
    }
    public void deletePublisher(Long publisherId) {
        publisherRepository.deleteById(publisherId);
    }
    public List<Publisher> getAllPublishers() {
        return publisherRepository.findAll();
    }
}
